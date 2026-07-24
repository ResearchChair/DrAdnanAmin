<?php

namespace App\Support;

use App\Models\Publication;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicationSummary
{
    /** @param  Collection<int, Publication>  $publications */
    public static function build(Collection $publications, ?int $totalCitations = null): array
    {
        $published = $publications->filter(fn (Publication $p) => (string) $p->status === 'published');
        $inProgressCount = $publications
            ->filter(fn (Publication $p): bool => (string) $p->status !== 'published')
            ->count();

        $byType = $published
            ->groupBy('type')
            ->map(fn (Collection $items, string $type) => [
                'type' => $type,
                'label' => config('academic.publication_types.'.$type, ucfirst(str_replace('_', ' ', $type))),
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        $byPublisher = $published
            ->groupBy(fn (Publication $p) => $p->resolvedPublisher())
            ->map(fn (Collection $items, string $publisher) => [
                'publisher' => $publisher,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        $byYear = $published
            ->filter(fn (Publication $p) => filled($p->year))
            ->groupBy('year')
            ->map(fn (Collection $items, $year) => [
                'year' => (int) $year,
                'count' => $items->count(),
            ])
            ->sortByDesc('year')
            ->values()
            ->all();

        $journalCount = $published->where('type', 'journal')->count();
        $conferenceCount = $published->where('type', 'conference')->count();
        $bookChapterCount = $published->whereIn('type', ['book_chapter', 'book'])->count();
        $yearSpan = collect($byYear)->pluck('year')->filter();

        // Merge book + book_chapter into one summary row for display.
        $byType = collect($byType)
            ->reject(fn (array $row) => in_array($row['type'], ['book', 'book_chapter'], true))
            ->values()
            ->all();

        if ($bookChapterCount > 0) {
            $byType[] = [
                'type' => 'book_chapter',
                'label' => 'Books & Chapters',
                'count' => $bookChapterCount,
            ];
            $byType = collect($byType)->sortByDesc('count')->values()->all();
        }

        return [
            'total' => $published->count(),
            'journal_count' => $journalCount,
            'conference_count' => $conferenceCount,
            'book_chapter_count' => $bookChapterCount,
            'in_progress_count' => $inProgressCount,
            'other_count' => $published->count() - $journalCount - $conferenceCount - $bookChapterCount,
            'total_citations' => $totalCitations ?? (int) $published->sum('citation_count'),
            'year_from' => $yearSpan->isNotEmpty() ? $yearSpan->min() : null,
            'year_to' => $yearSpan->isNotEmpty() ? $yearSpan->max() : null,
            'by_type' => $byType,
            'by_publisher' => $byPublisher,
            'by_year' => $byYear,
            'max_type' => collect($byType)->max('count') ?: 1,
            'max_publisher' => collect($byPublisher)->max('count') ?: 1,
            'max_year' => collect($byYear)->max('count') ?: 1,
        ];
    }

    public static function inferPublisher(?string $publisher, ?string $venue, ?string $doi): string
    {
        if (filled($publisher)) {
            return trim($publisher);
        }

        $haystack = Str::lower(trim(($venue ?? '').' '.($doi ?? '')));

        if ($haystack === '') {
            return 'Other / Unspecified';
        }

        foreach (config('academic.publication_publishers', []) as $label => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($haystack, Str::lower($keyword))) {
                    return $label;
                }
            }
        }

        if (filled($venue)) {
            return trim($venue);
        }

        return 'Other / Unspecified';
    }
}
