@extends('layouts.app')

@section('title', 'Publications | '.$profile->name)

@section('content')
<style>
    .pub-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        padding: 0.375rem;
        margin-bottom: 1.5rem;
        border-radius: 0.875rem;
        background: color-mix(in srgb, var(--accent) 5%, var(--surface-muted, #f5ebe8) 95%);
        border: 1px solid color-mix(in srgb, var(--accent) 12%, #fff 88%);
    }
    .pub-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5625rem 0.875rem;
        border-radius: 0.625rem;
        border: 1px solid transparent;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        background: transparent;
        cursor: pointer;
        transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .pub-tab:hover:not(.pub-tab--active) {
        color: var(--accent);
        background: color-mix(in srgb, var(--accent) 7%, #fff 93%);
    }
    .pub-tab--active {
        color: var(--accent);
        background: var(--surface, #fff9f5);
        border-color: color-mix(in srgb, var(--accent) 18%, #fff 82%);
        box-shadow: 0 1px 2px color-mix(in srgb, var(--accent) 10%, transparent 90%), 0 4px 12px color-mix(in srgb, var(--accent) 8%, transparent 92%);
    }
    .pub-tab__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.5rem;
        height: 1.5rem;
        padding: 0 0.4375rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: #64748b;
        background: color-mix(in srgb, var(--accent) 8%, #fff 92%);
    }
    .pub-tab--active .pub-tab__count { color: #fff; background: var(--accent); }
    .pub-stat-bar {
        height: 0.5rem;
        border-radius: 9999px;
        background: color-mix(in srgb, var(--accent) 12%, #e2e8f0 88%);
        overflow: hidden;
    }
    .pub-stat-bar > span {
        display: block;
        height: 100%;
        border-radius: 9999px;
        background: var(--accent);
    }
    .pub-year-grid {
        display: grid;
        grid-template-columns: 1fr;
        column-gap: 2rem;
        row-gap: 1rem;
    }
    @media (min-width: 640px) {
        .pub-year-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .pub-year-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            column-gap: 2.5rem;
        }
    }
    .pub-year-item {
        min-width: 0;
        padding-right: 0.25rem;
    }
    .pub-search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        align-items: stretch;
    }
    .pub-search-input {
        flex: 1 1 14rem;
        min-width: 0;
        min-height: 2.75rem;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        line-height: 1.4;
        color: #1e293b;
        background: #fff;
        border: 1px solid color-mix(in srgb, var(--accent) 18%, #cbd5e1 82%);
        border-radius: 0.5rem;
        outline: none;
        box-shadow: 0 1px 2px color-mix(in srgb, var(--accent) 6%, transparent 94%);
    }
    .pub-search-input::placeholder {
        color: #94a3b8;
    }
    .pub-search-input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 18%, transparent 82%);
    }
    .pub-search-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.75rem;
        padding: 0.625rem 1.125rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #fff;
        background: var(--accent);
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
    }
    .pub-search-btn:hover {
        opacity: 0.92;
    }
    .pub-search-clear {
        display: inline-flex;
        align-items: center;
        min-height: 2.75rem;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        color: #475569;
    }
    .pub-search-clear:hover {
        color: var(--accent);
    }
    .pub-recommend-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 0.75rem;
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        background: color-mix(in srgb, var(--accent) 5%, #fff 95%);
        border: 1px solid color-mix(in srgb, var(--accent) 12%, #e2e8f0 88%);
    }
    .pub-recommend-toolbar .pub-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.25rem;
        padding: 0.375rem 0.875rem;
        font-size: 0.8125rem;
        font-weight: 600;
        border-radius: 0.5rem;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .pub-recommend-toolbar .pub-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .pub-btn--primary {
        color: #fff;
        background: var(--accent);
    }
    .pub-btn--ghost {
        color: #475569;
        background: #fff;
        border-color: #cbd5e1;
    }
    .pub-btn--ghost:hover:not(:disabled) {
        color: var(--accent);
        border-color: color-mix(in srgb, var(--accent) 30%, #cbd5e1 70%);
    }
    .pub-recommend-row {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 0.875rem 1rem;
        border: 1px solid color-mix(in srgb, var(--accent) 10%, #e2e8f0 90%);
        background: var(--surface, #fff9f5);
    }
    .pub-recommend-row + .pub-recommend-row {
        margin-top: 0.5rem;
    }
    .pub-recommend-row input[type="checkbox"] {
        margin-top: 0.2rem;
        width: 1rem;
        height: 1rem;
        accent-color: var(--accent);
        cursor: pointer;
        flex-shrink: 0;
    }
    .pub-recommend-cite {
        margin-top: 0.35rem;
        font-size: 0.75rem;
        line-height: 1.45;
        color: #64748b;
        word-break: break-word;
    }
    .pub-copied-toast {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--accent);
    }
</style>

@php
    $summary = $publicationSummary;
    $recommendPayload = $recommendablePublications->map(fn ($p) => [
        'id' => $p->id,
        'citation' => $p->toShortCitation(),
    ])->values();
@endphp

<section
    class="max-w-6xl mx-auto px-4 sm:px-6 py-12"
    x-data="{
        tab: @js($defaultPublicationTab),
        selectedIds: [],
        copyFeedback: '',
        items: @js($recommendPayload),
        selectAll() {
            this.selectedIds = this.items.map(item => item.id);
        },
        clearSelection() {
            this.selectedIds = [];
        },
        selectedCount() {
            return this.selectedIds.length;
        },
        isSelected(id) {
            return this.selectedIds.includes(id);
        },
        toggle(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(x => x !== id);
            } else {
                this.selectedIds = [...this.selectedIds, id];
            }
        },
        citationsFor(ids) {
            const idSet = new Set(ids);
            return this.items
                .filter(item => idSet.has(item.id))
                .map((item, index) => (index + 1) + '. ' + item.citation)
                .join('\n');
        },
        async copyText(text, count) {
            if (!text) return;
            try {
                await navigator.clipboard.writeText(text);
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            this.copyFeedback = 'Copied ' + count + ' citation' + (count === 1 ? '' : 's');
            setTimeout(() => { this.copyFeedback = ''; }, 2500);
        },
        copySelected() {
            this.copyText(this.citationsFor(this.selectedIds), this.selectedIds.length);
        },
        copyAll() {
            const ids = this.items.map(item => item.id);
            this.copyText(this.citationsFor(ids), ids.length);
        }
    }"
>
    <div class="mb-6">
        <h1 class="font-serif text-3xl font-bold text-[var(--accent)] mb-2">Publications</h1>
        <p class="text-sm text-slate-600 max-w-3xl">
            A structured overview of research outputs
            @if($summary['year_from'] && $summary['year_to'])
                from {{ $summary['year_from'] }}–{{ $summary['year_to'] }}
            @endif
            — journal articles, conference papers, and works in progress.
        </p>
    </div>

    <form method="GET" class="pub-search-form">
        <input type="hidden" name="tab" :value="tab">
        <input
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Topics: graph, link prediction…"
            class="pub-search-input"
            aria-label="Search publications by one or more topics"
        >
        <button type="submit" class="pub-search-btn">Search</button>
        @if($search !== '')
            <a
                href="{{ route('publications') }}"
                class="pub-search-clear"
                :href="'{{ route('publications') }}' + (tab === 'recommend' ? '?tab=recommend' : '')"
            >Clear</a>
        @endif
    </form>

    <div class="pub-tabs" role="tablist" aria-label="Publication categories">
        <button type="button" role="tab" class="pub-tab" :class="{ 'pub-tab--active': tab === 'journals' }" @click="tab = 'journals'">
            <span>Journal Articles</span>
            <span class="pub-tab__count">{{ $journalPublications->count() }}</span>
        </button>
        <button type="button" role="tab" class="pub-tab" :class="{ 'pub-tab--active': tab === 'conferences' }" @click="tab = 'conferences'">
            <span>Conference Papers</span>
            <span class="pub-tab__count">{{ $conferencePublications->count() }}</span>
        </button>
        <button type="button" role="tab" class="pub-tab" :class="{ 'pub-tab--active': tab === 'book_chapters' }" @click="tab = 'book_chapters'">
            <span>Books & Chapters</span>
            <span class="pub-tab__count">{{ $bookChapterPublications->count() }}</span>
        </button>
        <button type="button" role="tab" class="pub-tab" :class="{ 'pub-tab--active': tab === 'in_progress' }" @click="tab = 'in_progress'">
            <span>In Progress</span>
            <span class="pub-tab__count">{{ $inProgressPublications->count() }}</span>
        </button>
        <button type="button" role="tab" class="pub-tab" :class="{ 'pub-tab--active': tab === 'summary' }" @click="tab = 'summary'">
            <span>Summary</span>
            <span class="pub-tab__count">{{ $summary['total'] }}</span>
        </button>
        <button type="button" role="tab" class="pub-tab" :class="{ 'pub-tab--active': tab === 'recommend' }" @click="tab = 'recommend'">
            <span>Recommend</span>
            <span class="pub-tab__count">{{ $recommendablePublications->count() }}</span>
        </button>
    </div>

    {{-- Journals --}}
    <div x-show="tab === 'journals'" x-cloak class="space-y-4">
        @forelse($journalPublications as $publication)
            @include('partials.publication-card', ['publication' => $publication])
        @empty
            <p class="text-sm text-slate-500">No journal articles found{{ $search ? ' for this search' : '' }}.</p>
        @endforelse
    </div>

    {{-- Conferences --}}
    <div x-show="tab === 'conferences'" x-cloak class="space-y-4">
        @forelse($conferencePublications as $publication)
            @include('partials.publication-card', ['publication' => $publication])
        @empty
            <p class="text-sm text-slate-500">No conference papers found{{ $search ? ' for this search' : '' }}.</p>
        @endforelse
    </div>

    {{-- Books & chapters --}}
    <div x-show="tab === 'book_chapters'" x-cloak class="space-y-4">
        @forelse($bookChapterPublications as $publication)
            @include('partials.publication-card', ['publication' => $publication])
        @empty
            <p class="text-sm text-slate-500">No books or book chapters found{{ $search ? ' for this search' : '' }}.</p>
        @endforelse
    </div>

    {{-- In progress --}}
    <div x-show="tab === 'in_progress'" x-cloak class="space-y-4">
        <p class="text-sm text-slate-500 mb-2">Manuscripts and papers currently under preparation or review. Add these manually in Admin → Publications with type <strong>In Progress</strong>.</p>
        @forelse($inProgressPublications as $publication)
            @include('partials.publication-card', ['publication' => $publication])
        @empty
            <p class="text-sm text-slate-500">No papers in progress listed yet.</p>
        @endforelse
    </div>

    {{-- Recommend (cross-type citation copy) --}}
    <div x-show="tab === 'recommend'" x-cloak>
        <p class="text-sm text-slate-600 mb-4 max-w-3xl">
            Search across journals, conferences, and books/chapters with one or more topics (comma-separated), then copy short citation lines to paste into peer-review comments.
            Example: <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">graph, link prediction</code>
        </p>

        <div class="pub-recommend-toolbar">
            <button type="button" class="pub-btn pub-btn--ghost" @click="selectAll()" :disabled="items.length === 0">Select all</button>
            <button type="button" class="pub-btn pub-btn--ghost" @click="clearSelection()" :disabled="selectedCount() === 0">Clear</button>
            <button type="button" class="pub-btn pub-btn--primary" @click="copySelected()" :disabled="selectedCount() === 0">
                Copy selected
                <span x-text="selectedCount() ? ' (' + selectedCount() + ')' : ''"></span>
            </button>
            <button type="button" class="pub-btn pub-btn--primary" @click="copyAll()" :disabled="items.length === 0">
                Copy all matches
                <span>({{ $recommendablePublications->count() }})</span>
            </button>
            <span class="pub-copied-toast" x-show="copyFeedback" x-text="copyFeedback" x-cloak></span>
        </div>

        @forelse($recommendablePublications as $publication)
            <label class="pub-recommend-row">
                <input
                    type="checkbox"
                    :checked="isSelected({{ $publication->id }})"
                    @change="toggle({{ $publication->id }})"
                >
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--accent)] bg-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] px-2 py-0.5 rounded">
                            {{ $publication->type_label }}
                        </span>
                        @if($publication->year)
                            <span class="text-xs text-slate-500 tabular-nums">{{ $publication->year }}</span>
                        @endif
                    </div>
                    <div class="text-sm font-medium text-slate-800 leading-snug">{{ $publication->title }}</div>
                    <div class="pub-recommend-cite">{{ $publication->toShortCitation() }}</div>
                </div>
            </label>
        @empty
            <p class="text-sm text-slate-500">No matching publications{{ $search ? ' for this search' : '' }}.</p>
        @endforelse
    </div>

    {{-- Summary --}}
    <div x-show="tab === 'summary'" x-cloak>
        <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-6 mb-6">
            <h2 class="font-serif text-xl font-bold text-[var(--accent)] mb-2">Publication summary</h2>
            <p class="text-sm text-slate-600 leading-relaxed max-w-3xl">
                This overview summarizes the publication record across journals, conferences, and other outlets
                @if($summary['year_from'] && $summary['year_to'])
                    spanning {{ $summary['year_from'] }} to {{ $summary['year_to'] }}
                @endif
                . Counts are derived from the visible publication list
                @if($search !== '')
                    (filtered by current search)
                @endif
                .
            </p>

            <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-3xl font-bold text-[var(--accent)] tabular-nums">{{ $summary['total'] }}</div>
                    <div class="mt-1 text-[0.6875rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Published</div>
                </div>
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-3xl font-bold text-[var(--accent)] tabular-nums">{{ $summary['journal_count'] }}</div>
                    <div class="mt-1 text-[0.6875rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Journals</div>
                </div>
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-3xl font-bold text-[var(--accent)] tabular-nums">{{ $summary['conference_count'] }}</div>
                    <div class="mt-1 text-[0.6875rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Conferences</div>
                </div>
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-3xl font-bold text-[var(--accent)] tabular-nums">{{ $summary['book_chapter_count'] }}</div>
                    <div class="mt-1 text-[0.6875rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Books & Chapters</div>
                </div>
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-3xl font-bold text-[var(--accent)] tabular-nums">{{ number_format($summary['total_citations']) }}</div>
                    <div class="mt-1 text-[0.6875rem] font-semibold uppercase tracking-[0.15em] text-slate-500">Citations</div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- By type --}}
            <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-5">
                <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-4">By publication type</h3>
                @forelse($summary['by_type'] as $row)
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-center justify-between gap-3 text-sm mb-1.5">
                            <span class="font-medium text-slate-700">{{ $row['label'] }}</span>
                            <span class="tabular-nums text-slate-500">{{ $row['count'] }}</span>
                        </div>
                        <div class="pub-stat-bar">
                            <span style="width: {{ max(4, round(($row['count'] / $summary['max_type']) * 100)) }}%"></span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No published works to summarize.</p>
                @endforelse
                @if($summary['in_progress_count'] > 0)
                    <p class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500">
                        Plus {{ $summary['in_progress_count'] }} paper{{ $summary['in_progress_count'] === 1 ? '' : 's' }} currently in progress.
                    </p>
                @endif
            </div>

            {{-- By publisher --}}
            <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-5">
                <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-4">By publisher / outlet</h3>
                @forelse($summary['by_publisher'] as $row)
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-center justify-between gap-3 text-sm mb-1.5">
                            <span class="font-medium text-slate-700 truncate" title="{{ $row['publisher'] }}">{{ $row['publisher'] }}</span>
                            <span class="tabular-nums text-slate-500 shrink-0">{{ $row['count'] }}</span>
                        </div>
                        <div class="pub-stat-bar">
                            <span style="width: {{ max(4, round(($row['count'] / $summary['max_publisher']) * 100)) }}%"></span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No publisher data available.</p>
                @endforelse
            </div>

            {{-- By year --}}
            <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-5 lg:col-span-2">
                <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-4">By year</h3>
                @if(count($summary['by_year']) > 0)
                    <div class="pub-year-grid">
                        @foreach($summary['by_year'] as $row)
                            <div class="pub-year-item">
                                <div class="flex items-center justify-between gap-3 text-sm mb-1.5">
                                    <span class="font-medium text-slate-700 tabular-nums">{{ $row['year'] }}</span>
                                    <span class="tabular-nums text-slate-500 shrink-0">{{ $row['count'] }} paper{{ $row['count'] === 1 ? '' : 's' }}</span>
                                </div>
                                <div class="pub-stat-bar">
                                    <span style="width: {{ max(4, round(($row['count'] / $summary['max_year']) * 100)) }}%"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">No year data available.</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
