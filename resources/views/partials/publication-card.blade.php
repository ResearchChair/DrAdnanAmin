<div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
    <div class="flex flex-wrap items-center gap-2 mb-2">
        <span class="text-xs font-semibold uppercase tracking-wide bg-[var(--accent)]/10 text-[var(--accent)] px-2 py-0.5 rounded">{{ $publication->type_label }}</span>
        @if($publication->year)
            <span class="text-sm text-slate-500">{{ $publication->year }}</span>
        @endif
        @if($publication->citation_count)
            <span class="text-sm text-slate-400">&middot; {{ $publication->citation_count }} citations</span>
        @endif
    </div>
    <h3 class="font-semibold text-slate-800 leading-snug">{{ $publication->title }}</h3>
    @if($publication->authors)
        <p class="text-sm text-slate-600 mt-1">{{ $publication->authors }}</p>
    @endif
    @if($publication->venue)
        <p class="text-sm text-slate-500 mt-1 italic">{{ $publication->venue }}</p>
    @endif
    <div class="flex gap-3 mt-3">
        @if($publication->doi_url)
            <a href="{{ $publication->doi_url }}" target="_blank" rel="noopener" class="text-xs text-[var(--secondary)] hover:underline">DOI</a>
        @endif
        @if($publication->pdf_url)
            <a href="{{ $publication->pdf_url }}" target="_blank" rel="noopener" class="text-xs text-[var(--secondary)] hover:underline">PDF</a>
        @endif
        @if($publication->url)
            <a href="{{ $publication->url }}" target="_blank" rel="noopener" class="text-xs text-[var(--secondary)] hover:underline">Link</a>
        @endif
    </div>
</div>
