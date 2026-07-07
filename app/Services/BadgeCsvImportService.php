<?php

namespace App\Services;

use App\Models\EarnedBadge;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BadgeCsvImportService
{
    /**
     * @param  array<string, string>  $logoFiles  Map of basename => storage path (e.g. oracle.png => imports/badges/oracle.png)
     */
    public function import(string $csvContents, array $logoFiles = []): array
    {
        $rows = $this->parseCsv($csvContents);
        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));

            if ($title === '') {
                $skipped++;

                continue;
            }

            $issuer = trim((string) ($row['issuer'] ?? '')) ?: null;
            $year = filled($row['year'] ?? null) ? (int) $row['year'] : null;
            $url = trim((string) ($row['url'] ?? '')) ?: null;
            $logoPath = $this->resolveLogoPath(trim((string) ($row['logo'] ?? '')), $logoFiles);

            $badge = EarnedBadge::query()
                ->where('title', $title)
                ->when($issuer, fn ($q) => $q->where('issuer', $issuer), fn ($q) => $q->whereNull('issuer'))
                ->first();

            $payload = [
                'title' => $title,
                'issuer' => $issuer,
                'year' => $year,
                'url' => $url,
                'is_visible' => true,
            ];

            if ($logoPath) {
                $payload['logo_path'] = $logoPath;
            }

            if ($badge) {
                $badge->fill($payload)->save();
                $updated++;
            } else {
                EarnedBadge::query()->create($payload);
                $added++;
            }
        }

        return compact('added', 'updated', 'skipped');
    }

    /** @return list<array<string, string>> */
    protected function parseCsv(string $contents): array
    {
        $contents = trim($contents);

        if ($contents === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $contents) ?: [];
        $header = null;
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $cells = str_getcsv($line);

            if ($header === null) {
                $header = array_map(fn ($h) => Str::lower(trim((string) $h)), $cells);

                continue;
            }

            $row = [];

            foreach ($header as $index => $key) {
                $row[$key] = $cells[$index] ?? '';
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  array<string, string>  $logoFiles
     */
    protected function resolveLogoPath(string $logoFilename, array $logoFiles): ?string
    {
        if ($logoFilename === '') {
            return null;
        }

        $basename = basename($logoFilename);

        if (! isset($logoFiles[$basename])) {
            return null;
        }

        $source = $logoFiles[$basename];
        $disk = Storage::disk('public');
        $target = 'badges/'.Str::uuid().'-'.$basename;

        if ($disk->exists($source)) {
            $disk->copy($source, $target);

            return $target;
        }

        return null;
    }
}
