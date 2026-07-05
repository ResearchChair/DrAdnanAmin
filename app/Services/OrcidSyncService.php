<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Publication;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

        $orcidId = str_replace('https://orcid.org/', '', trim($orcidId));

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->get("https://pub.orcid.org/v3.0/{$orcidId}/works");
        } catch (\Throwable $e) {
            return ['added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['ORCID request failed: '.$e->getMessage()]];
        }

        if (! $response->successful()) {
            return ['added' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['Failed to fetch ORCID works.']];
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

            $publication->fill([
                'title' => $title,
                'year' => $year,
                'doi' => $doi,
                'external_id_orcid' => $putCode,
            ]);

            if ($doi) {
                $enriched = $this->openAlex->lookupByDoi($doi);

                if ($enriched) {
                    $publication->fill([
                        'venue' => $enriched['venue'] ?? $publication->venue,
                        'authors' => $enriched['authors'] ?? $publication->authors,
                        'citation_count' => $enriched['citation_count'] ?? $publication->citation_count,
                        'external_id_openalex' => $enriched['openalex_id'] ?? $publication->external_id_openalex,
                        'type' => $enriched['type'] ?? $publication->type,
                        'url' => $enriched['url'] ?? $publication->url,
                    ]);
                }
            }

            $publication->is_visible = true;
            $publication->save();
        }

        if ($profile) {
            $profile->update(['orcid_id' => $orcidId]);
        }

        return compact('added', 'updated', 'skipped', 'errors');
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
}
