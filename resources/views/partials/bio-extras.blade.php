@if($profile->hasResearchArticles() || $profile->flyerHighlightsList() !== [])
<div
    class="space-y-8"
    x-data="{
        modal: null,
        copied: false,
        copiedAll: false,
        openArticle() {
            this.modal = {
                type: 'article',
                title: 'Biography for Article',
                body: @js($profile->research_articles_html),
                html: true,
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
        },
        copyAllHighlights() {
            navigator.clipboard.writeText(@js($profile->flyerHighlightsPlainText())).then(() => {
                this.copiedAll = true;
                setTimeout(() => this.copiedAll = false, 2000);
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
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ str(strip_tags($profile->research_articles_html))->squish()->limit(120) }}</p>
                    <p class="text-xs text-[var(--secondary)] mt-2 font-medium">Click to read full article biography</p>
                </div>
                <svg class="w-5 h-5 text-slate-400 group-hover:text-[var(--accent)] shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    @endif

    @if($profile->flyerHighlightsList() !== [])
        <div>
            <h3 class="section-heading font-serif text-xl font-bold text-[var(--accent)] mb-4">Flyer Highlights</h3>
            <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-4">
                <div class="flex items-start justify-between gap-3 mb-4 pb-3 border-b border-slate-200/80">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 shrink-0 flex items-center justify-center bg-[var(--accent)]/10 text-[var(--accent)]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-[var(--accent)]">Copy-ready highlights</p>
                            <p class="text-xs text-slate-500 mt-0.5">For flyers, brochures, and event materials.</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="copyAllHighlights()"
                        class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-[var(--accent)] border border-slate-200 bg-white hover:bg-slate-50 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-text="copiedAll ? 'Copied!' : 'Copy all'"></span>
                    </button>
                </div>
                <ul class="space-y-3">
                    @foreach($profile->flyerHighlightsList() as $highlight)
                        <li class="flex items-start gap-2.5 text-sm text-slate-700 leading-relaxed">
                            <span class="text-[var(--secondary)] mt-1 shrink-0 font-bold leading-none">&#9679;</span>
                            <span class="min-w-0">
                                @if($highlight['title'])
                                    <span class="font-semibold text-slate-800">{{ $highlight['title'] }}</span>
                                    <span class="text-slate-600"> — {{ $highlight['content'] }}</span>
                                @else
                                    {{ $highlight['content'] }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
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
            {{-- Article modal: photo + download --}}
            <template x-if="modal?.type === 'article'">
                <div class="flex items-start gap-4 p-5 border-b border-slate-200 shrink-0">
                    <div class="shrink-0">
                        @if($profile->photoUrl())
                            <img src="{{ $profile->photoUrl() }}" alt="{{ $profile->name }}" class="w-20 h-24 object-cover object-top border border-slate-200">
                            <a
                                href="{{ $profile->photoUrl() }}"
                                download="{{ \Illuminate\Support\Str::slug($profile->name) }}-photo.{{ pathinfo($profile->photo_path, PATHINFO_EXTENSION) ?: 'jpg' }}"
                                class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-[var(--accent)] hover:text-[var(--secondary)] transition-colors"
                                @click.stop
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download photo
                            </a>
                        @else
                            <div class="w-20 h-24 bg-[var(--accent)]/10 flex items-center justify-center font-serif text-3xl font-bold text-[var(--accent)]">
                                {{ substr($profile->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1 pt-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[var(--secondary)]">{{ $profile->name }}@if($profile->credentials), {{ $profile->credentials }}@endif</p>
                        <h4 class="font-serif text-xl font-bold text-[var(--accent)] mt-1" x-text="modal?.title"></h4>
                        @if($profile->title)
                            <p class="text-sm text-slate-500 mt-1">{{ $profile->title }}</p>
                        @endif
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 text-2xl leading-none shrink-0" aria-label="Close">&times;</button>
                </div>
            </template>

            <div class="p-5 overflow-y-auto flex-1">
                <div class="prose-academic prose-academic-rich text-base" x-html="modal.body"></div>
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
