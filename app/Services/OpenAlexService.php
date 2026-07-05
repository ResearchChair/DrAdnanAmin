<?php

namespace App\Services;

use App\Models\Publication;
use App\Support\AcademicHttp;
use Illuminate\Support\Str;

class OpenAlexService
{
    public function __construct(
        protected string $baseUrl = 'https://api.openalex.org',
        protected string $email = '',
    ) {
        $this->email = config('academic.openalex_email', 'portfolio@example.com');
    }

    public function lookupByDoi(string $doi): ?array
    {
        $doi = $this->normalizeDoi($doi);

        if (! $doi) {
            return null;
        }

        $response = AcademicHttp::client()
            ->withHeaders(['User-Agent' => "AcademicPortfolio/1.0 (mailto:{$this->email})"])
            ->get("{$this->baseUrl}/works/doi:{$doi}");

        if (! $response->successful()) {
            return null;
        }

        return $this->mapWork($response->json());
    }

    public function fetchWorksByOrcid(string $orcidId): array
    {
        $orcidId = $this->normalizeOrcid($orcidId);
        $works = [];
        $cursor = '*';

        do {
            $response = AcademicHttp::client()
                ->withHeaders(['User-Agent' => "AcademicPortfolio/1.0 (mailto:{$this->email})"])
                ->get("{$this->baseUrl}/works", [
                    'filter' => "author.orcid:https://orcid.org/{$orcidId}",
                    'per_page' => 200,
                    'cursor' => $cursor,
                ]);

            if (! $response->successful()) {
                break;
            }

            $payload = $response->json();
            foreach ($payload['results'] ?? [] as $work) {
                $works[] = $this->mapWork($work);
            }

            $cursor = $payload['meta']['next_cursor'] ?? null;
        } while ($cursor);

        return array_filter($works);
    }

    public function enrichPublication(Publication $publication): bool
    {
        if (! $publication->doi) {
            return false;
        }

        $data = $this->lookupByDoi($publication->doi);

        if (! $data) {
            return false;
        }

        $publication->fill([
            'title' => $data['title'] ?? $publication->title,
            'year' => $data['year'] ?? $publication->year,
            'venue' => $data['venue'] ?? $publication->venue,
            'authors' => $data['authors'] ?? $publication->authors,
            'citation_count' => $data['citation_count'] ?? $publication->citation_count,
            'external_id_openalex' => $data['openalex_id'] ?? $publication->external_id_openalex,
            'type' => $data['type'] ?? $publication->type,
            'url' => $data['url'] ?? $publication->url,
        ])->save();

        return true;
    }

    protected function mapWork(array $work): ?array
    {
        $title = $work['title'] ?? $work['display_name'] ?? null;

        if (! $title) {
            return null;
        }

        $doi = $work['doi'] ?? ($work['ids']['doi'] ?? null);
        $doi = $doi ? $this->normalizeDoi(str_replace('https://doi.org/', '', $doi)) : null;

        $authors = collect($work['authorships'] ?? [])
            ->map(fn ($authorship) => $authorship['author']['display_name'] ?? null)
            ->filter()
            ->implode(', ');

        return [
            'title' => $title,
            'doi' => $doi,
            'year' => $work['publication_year'] ?? null,
            'venue' => $work['primary_location']['source']['display_name'] ?? ($work['host_venue']['display_name'] ?? null),
            'authors' => $authors ?: null,
            'citation_count' => $work['cited_by_count'] ?? 0,
            'openalex_id' => $work['id'] ?? null,
            'type' => $this->mapType($work['type'] ?? 'other'),
            'url' => $work['id'] ?? null,
        ];
    }

    protected function mapType(string $type): string
    {
        return match ($type) {
            'article', 'review', 'letter' => 'journal',
            'proceedings-article', 'conference' => 'conference',
            'book-chapter' => 'book_chapter',
            'preprint' => 'preprint',
            'book' => 'book',
            default => 'other',
        };
    }

    protected function normalizeDoi(string $doi): ?string
    {
        $doi = trim(str_replace(['https://doi.org/', 'http://doi.org/'], '', $doi));

        return $doi !== '' ? $doi : null;
    }

    protected function normalizeOrcid(string $orcidId): string
    {
        return str_replace('https://orcid.org/', '', trim($orcidId));
    }
}
