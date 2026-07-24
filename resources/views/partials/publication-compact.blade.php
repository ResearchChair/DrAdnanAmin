@if($publication->authors)
    <p class="text-xs text-slate-500 mt-1 leading-snug">{{ $publication->authors }}</p>
@endif
<p class="text-sm text-slate-700 leading-snug mt-1">
    <span class="font-medium text-[var(--accent)]">{{ $publication->title }}</span>
</p>
<p class="text-xs mt-1">
    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 font-semibold uppercase tracking-wide text-slate-600">{{ $publication->status_label }}</span>
</p>
<p class="text-xs text-slate-500 mt-1 italic">
    {{ $publication->venue }}@if($publication->year), {{ $publication->year }}@endif
    @if($publication->citation_count)
        <span class="not-italic text-slate-400">&middot; {{ $publication->citation_count }} citations</span>
    @endif
</p>
@if($publication->doi_url)
    <a href="{{ $publication->doi_url }}" target="_blank" rel="noopener" class="inline-block mt-1 text-xs text-[var(--secondary)] hover:underline">DOI</a>
@endif
