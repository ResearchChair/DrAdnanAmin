@if($profile->hasResearchArticles() || $profile->flyerHighlightsList() !== [])
<div
    class="mt-10 pt-8 border-t border-slate-200 space-y-8"
    x-data="{
        modal: null,
        copied: false,
        openArticle() {
            this.modal = {
                title: 'Biography for Article',
                body: @js($profile->research_articles_html),
                html: true,
            };
            this.copied = false;
        },
        openHighlight(title, content) {
            this.modal = {
                title: title || 'Flyer Highlight',
                body: content,
                html: false,
            };
            this.copied = false;
        },
        closeModal() {
            this.modal = null;
            this.copied = false;
        },
        copyModalText() {
            if (! this.modal) return;
            const text = this.modal.html
                ? new DOMParser().parseFromString(this.modal.body, 'text/html').body.textContent || ''
                : this.modal.body;
            navigator.clipboard.writeText(text.trim()).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        }
    }"
    @keydown.escape.window="closeModal()"
>
    @if($profile->hasResearchArticles())
        <div>
            <h3 class="section-heading font-serif text-xl font-bold text-[var(--accent)] mb-4">Biography for Article</h3>
            <button
                type="button"
                @click="openArticle()"
                class="group w-full text-left theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] hover:border-[var(--accent)]/30 hover:shadow-sm transition-all p-4 flex items-center gap-4"
            >
                @if($profile->photoUrl())
                    <img src="{{ $profile->photoUrl() }}" alt="{{ $profile->name }}" class="w-16 h-20 object-cover object-top shrink-0 border border-slate-200">
                @else
                    <div class="w-16 h-20 shrink-0 bg-[var(--accent)]/10 flex items-center justify-center font-serif text-2xl font-bold text-[var(--accent)]">
                        {{ substr($profile->name, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-[var(--accent)] group-hover:text-[var(--secondary)] transition-colors">{{ $profile->name }}</p>
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ str(strip_tags($profile->research_articles_html))->squish()->limit(140) }}</p>
                    <p class="text-xs text-[var(--secondary)] mt-2 font-medium">Click to read full article biography</p>
                </div>
                <svg class="w-5 h-5 text-slate-400 group-hover:text-[var(--accent)] shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    @endif

    @if($profile->flyerHighlightsList() !== [])
        <div>
            <div class="mb-4">
                <h3 class="section-heading font-serif text-xl font-bold text-[var(--accent)] mb-1">Flyer Highlights</h3>
                <p class="text-slate-500 text-sm">Click any highlight to open, read, and copy for flyers or event materials.</p>
            </div>
            <div class="space-y-2">
                @foreach($profile->flyerHighlightsList() as $highlight)
                    <button
                        type="button"
                        @click="openHighlight(@js($highlight['title']), @js($highlight['content']))"
                        class="group w-full text-left theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] hover:border-[var(--accent)]/25 hover:shadow-sm transition-all p-4 flex items-center gap-4"
                    >
                        @if($profile->photoUrl())
                            <img src="{{ $profile->photoUrl() }}" alt="" class="w-12 h-12 object-cover object-top shrink-0 rounded-full border border-slate-200" aria-hidden="true">
                        @endif
                        <div class="min-w-0 flex-1">
                            @if($highlight['title'])
                                <p class="text-xs font-semibold uppercase tracking-wider text-[var(--secondary)] mb-1">{{ $highlight['title'] }}</p>
                            @endif
                            <p class="text-slate-700 text-sm leading-relaxed line-clamp-2">{{ $highlight['content'] }}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-[var(--accent)] shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Modal --}}
    <div
        x-show="modal"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
        @click.self="closeModal()"
    >
        <div
            x-show="modal"
            x-transition
            class="theme-surface w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl border border-[color-mix(in_srgb,var(--accent)_15%,#fff_85%)]"
            @click.stop
        >
            <div class="flex items-start gap-4 p-5 border-b border-slate-200 shrink-0">
                @if($profile->photoUrl())
                    <img src="{{ $profile->photoUrl() }}" alt="{{ $profile->name }}" class="w-20 h-24 object-cover object-top shrink-0 border border-slate-200">
                @else
                    <div class="w-20 h-24 shrink-0 bg-[var(--accent)]/10 flex items-center justify-center font-serif text-3xl font-bold text-[var(--accent)]">
                        {{ substr($profile->name, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1 pt-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[var(--secondary)]">{{ $profile->name }}@if($profile->credentials), {{ $profile->credentials }}@endif</p>
                    <h4 class="font-serif text-xl font-bold text-[var(--accent)] mt-1" x-text="modal?.title"></h4>
                    @if($profile->title)
                        <p class="text-sm text-slate-500 mt-1">{{ $profile->title }}</p>
                    @endif
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 text-2xl leading-none shrink-0" aria-label="Close">&times;</button>
            </div>
            <div class="p-5 overflow-y-auto flex-1">
                <template x-if="modal?.html">
                    <div class="prose-academic prose-academic-rich text-base" x-html="modal.body"></div>
                </template>
                <template x-if="modal && !modal.html">
                    <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line" x-text="modal.body"></p>
                </template>
            </div>
            <div class="flex items-center justify-end gap-3 p-4 border-t border-slate-200 shrink-0 bg-slate-50/50">
                <button
                    type="button"
                    @click="copyModalText()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-[var(--accent)] border border-slate-200 bg-white hover:bg-slate-50 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span x-text="copied ? 'Copied!' : 'Copy text'"></span>
                </button>
                <button
                    type="button"
                    @click="closeModal()"
                    class="px-4 py-2 text-sm font-medium text-white bg-[var(--accent)] hover:opacity-90 transition-opacity"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
