@if($profile->hasResearchArticles() || $profile->flyerHighlightsList() !== [])
<div class="mt-10 pt-8 border-t border-slate-200 space-y-10" x-data="{
    copiedId: null,
    copyText(text, id) {
        navigator.clipboard.writeText(text).then(() => {
            this.copiedId = id;
            setTimeout(() => { if (this.copiedId === id) this.copiedId = null; }, 2000);
        });
    }
}">
    @if($profile->hasResearchArticles())
        <div>
            <h3 class="section-heading font-serif text-xl font-bold text-[var(--accent)] mb-4">Research Articles</h3>
            <div class="prose-academic prose-academic-rich text-base">
                {!! $profile->research_articles_html !!}
            </div>
        </div>
    @endif

    @if($profile->flyerHighlightsList() !== [])
        <div>
            <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
                <div>
                    <h3 class="section-heading font-serif text-xl font-bold text-[var(--accent)] mb-1">Flyer Highlights</h3>
                    <p class="text-slate-500 text-sm">Copy-ready text for brochures, events, and sharing with colleagues.</p>
                </div>
                <button
                    type="button"
                    @click="copyText(@js($profile->flyerHighlightsPlainText()), 'all')"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[var(--accent)] hover:text-[var(--secondary)] transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span x-text="copiedId === 'all' ? 'Copied!' : 'Copy all'"></span>
                </button>
            </div>
            <div class="space-y-3">
                @foreach($profile->flyerHighlightsList() as $highlight)
                    @php($copyId = 'highlight-'.$loop->index)
                    <div class="relative theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 pr-28">
                        @if($highlight['title'])
                            <p class="text-xs font-semibold uppercase tracking-wider text-[var(--secondary)] mb-2">{{ $highlight['title'] }}</p>
                        @endif
                        <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line">{{ $highlight['content'] }}</p>
                        <button
                            type="button"
                            @click="copyText(@js($highlight['content']), '{{ $copyId }}')"
                            class="absolute top-3 right-3 inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-[var(--accent)] bg-white border border-slate-200 hover:border-[var(--accent)]/30 hover:bg-slate-50 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copiedId === '{{ $copyId }}' ? 'Copied' : 'Copy'"></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endif
