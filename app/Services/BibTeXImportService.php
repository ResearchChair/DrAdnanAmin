<?php

namespace App\Services;

use App\Models\Publication;
use Illuminate\Support\Str;

class BibTeXImportService
{
    public function import(string $contents): array
    {
        $entries = $this->parse($contents);
        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($entries as $entry) {
            if (empty($entry['title'])) {
                $skipped++;

                continue;
            }

            $publication = Publication::query()
                ->when(! empty($entry['doi']), fn ($q) => $q->where('doi', $entry['doi']))
                ->when(empty($entry['doi']), fn ($q) => $q->where('title', $entry['title']))
                ->first();

            if ($publication) {
                $publication->fill($entry)->save();
                $updated++;
            } else {
                Publication::query()->create(array_merge($entry, ['is_visible' => true]));
                $added++;
            }
        }

        return compact('added', 'updated', 'skipped');
    }

    protected function parse(string $contents): array
    {
        $entries = [];
        preg_match_all('/@\w+\s*\{([^,]+),([^@]*)\}/s', $contents, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $fieldsRaw = $match[2];
            $fields = [];
            preg_match_all('/(\w+)\s*=\s*\{([^}]*)\}/', $fieldsRaw, $fieldMatches, PREG_SET_ORDER);

            foreach ($fieldMatches as $fieldMatch) {
                $fields[strtolower($fieldMatch[1])] = trim($fieldMatch[2]);
            }

            if (empty($fields)) {
                continue;
            }

            $entries[] = [
                'title' => $fields['title'] ?? null,
                'authors' => $fields['author'] ?? ($fields['authors'] ?? null),
                'year' => isset($fields['year']) ? (int) $fields['year'] : null,
                'venue' => $fields['journal'] ?? ($fields['booktitle'] ?? ($fields['publisher'] ?? null)),
                'doi' => isset($fields['doi']) ? str_replace(['https://doi.org/', 'http://doi.org/'], '', $fields['doi']) : null,
                'url' => $fields['url'] ?? null,
                'type' => $this->mapEntryType($match[0]),
            ];
        }

        return $entries;
    }

    protected function mapEntryType(string $raw): string
    {
        $type = Str::lower(Str::before($raw, '{'));

        return match (trim(str_replace('@', '', $type))) {
            'article' => 'journal',
            'inproceedings', 'conference' => 'conference',
            'incollection', 'inbook' => 'book_chapter',
            'book' => 'book',
            'misc', 'unpublished' => 'preprint',
            default => 'other',
        };
    }
}
