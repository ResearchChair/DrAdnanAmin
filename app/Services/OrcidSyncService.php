<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Publication;
use App\Support\AcademicHttp;
use Illuminate\Support\Facades\Log;

class OrcidSyncService
{
    public function __construct(
        protected OpenAlexService $openAlex,
    ) {}

    public function sync(?string $orcidId = null): array
    {
        $profile = Profile::query()->first();
        $orcidId = $orcidId ?: ($profile?->orcid_id ?: config('academic.orcid_id'));

        if (! $orcidId) {
            return ['added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['ORCID ID is not configured.']];
        }

        $orcidId = self::normalizeOrcid($orcidId);

        if (! $orcidId) {
            return ['added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['ORCID ID format is invalid. Use 0000-0002-1234-5678.']];
        }

        if (self::isPlaceholderOrcid($orcidId)) {
            return ['added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['ORCID ID is still the placeholder. Enter your real ORCID on the profile.']];
        }

        $url = "https://pub.orcid.org/v3.0/{$orcidId}/works";

        try {
            $response = AcademicHttp::externalClient()
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('ORCID works request exception', [
                'orcid' => $orcidId,
                'message' => $e->getMessage(),
            ]);

            return ['added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [self::sslHint($e->getMessage())]];
        }

        if (! $response->successful()) {
            $error = self::describeHttpFailure($response->status(), $response->body(), $orcidId);

            Log::warning('ORCID works request failed', [
                'orcid' => $orcidId,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
            ]);

            return ['added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [$error]];
        }

        $groups = $response->json()['group'] ?? [];
        $added = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($groups as $group) {
            $summary = $group['work-summary'][0] ?? null;

            if (! $summary) {
                $skipped++;

                continue;
            }

            $title = $summary['title']['title']['value'] ?? null;
            $year = $summary['publication-date']['year']['value'] ?? null;
            $doi = $this->extractDoi($summary);
            $putCode = (string) ($summary['put-code'] ?? '');

            if (! $title) {
                $skipped++;

                continue;
            }

            $publication = $this->findExisting($doi, $title, $year);
            $isNew = ! $publication;

            if ($isNew) {
                $publication = new Publication;
                $added++;
            } else {
                $updated++;
            }

            $orcidType = $this->mapOrcidType($summary['type'] ?? null);

            $publication->fill([
                'title' => $title,
                'year' => $year,
                'doi' => $doi,
                'external_id_orcid' => $putCode,
                'type' => $orcidType,
                'venue' => $summary['journal-title']['value'] ?? $publication->venue,
            ]);

            if ($doi) {
                $enriched = $this->openAlex->lookupByDoi($doi);

                if ($enriched) {
                    $publication->fill([
                        'venue' => $enriched['venue'] ?? $publication->venue,
                        'authors' => $enriched['authors'] ?? $publication->authors,
                        'citation_count' => $enriched['citation_count'] ?? $publication->citation_count,
                        'external_id_openalex' => $enriched['openalex_id'] ?? $publication->external_id_openalex,
                        // Prefer ORCID work type for classification; OpenAlex often mislabels conference papers as journal articles.
                        'type' => $this->preferOrcidType($orcidType, $enriched['type'] ?? null),
                        'url' => $enriched['url'] ?? $publication->url,
                    ]);
                }
            }

            if (! filled($publication->type)) {
                $publication->type = 'other';
            }

            $publication->is_visible = true;
            $publication->save();
        }

        if ($profile) {
            $profile->update(['orcid_id' => $orcidId]);
        }

        return compact('added', 'updated', 'skipped', 'errors');
    }

    public static function normalizeOrcid(?string $orcidId): ?string
    {
        if (! $orcidId) {
            return null;
        }

        $orcidId = str_replace('https://orcid.org/', '', trim($orcidId));

        if (! preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/i', $orcidId)) {
            return null;
        }

        return strtoupper($orcidId);
    }

    public static function isPlaceholderOrcid(string $orcidId): bool
    {
        return in_array($orcidId, [
            '0000-0000-0000-0000',
            '0000-0000-0000-000X',
        ], true);
    }

    protected static function describeHttpFailure(int $status, string $body, string $orcidId): string
    {
        return match ($status) {
            404 => "ORCID record not found for {$orcidId}. Check the ID on orcid.org and update Site Content → Profile → Academic IDs.",
            401, 403 => 'ORCID API rejected the request (HTTP '.$status.'). Try again later.',
            429 => 'ORCID rate limit reached. Wait a few minutes and sync again.',
            default => 'Failed to fetch ORCID works (HTTP '.$status.'). '.self::sslHint($body),
        };
    }

    protected static function sslHint(string $message): string
    {
        if (stripos($message, 'SSL') !== false || stripos($message, 'certificate') !== false) {
            return 'SSL certificate error. On WAMP, ensure storage/app/cacert.pem exists or set ACADEMIC_HTTP_VERIFY=false in .env for local dev only.';
        }

        return 'Run: php artisan portfolio:test-orcid';
    }

    protected function extractDoi(array $summary): ?string
    {
        foreach ($summary['external-ids']['external-id'] ?? [] as $externalId) {
            if (($externalId['external-id-type'] ?? '') === 'doi') {
                return $externalId['external-id-value'] ?? null;
            }
        }

        return null;
    }

    protected function findExisting(?string $doi, string $title, ?int $year): ?Publication
    {
        if ($doi) {
            $match = Publication::query()->where('doi', $doi)->first();

            if ($match) {
                return $match;
            }
        }

        return Publication::query()
            ->where('title', $title)
            ->when($year, fn ($query) => $query->where('year', $year))
            ->first();
    }

    protected function mapOrcidType(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        return match ($type) {
            'journal-article', 'journal-issue', 'review', 'magazine-article', 'newsletter-article' => 'journal',
            'conference-paper', 'conference-abstract', 'conference-poster', 'conference-presentation',
            'conference-output', 'conference-proceedings' => 'conference',
            'book-chapter' => 'book_chapter',
            'book', 'edited-book' => 'book',
            'preprint', 'working-paper' => 'preprint',
            default => filled($type) ? 'other' : 'other',
        };
    }

    /**
     * Prefer ORCID classification for conference/book types when OpenAlex mislabels them as journal articles.
     */
    protected function preferOrcidType(string $orcidType, ?string $openAlexType): string
    {
        if (in_array($orcidType, ['conference', 'book_chapter', 'book', 'preprint'], true)) {
            return $orcidType;
        }

        if (filled($openAlexType) && $openAlexType !== 'other') {
            return $openAlexType;
        }

        return $orcidType ?: 'other';
    }
}
