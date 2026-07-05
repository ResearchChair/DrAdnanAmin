@if($youtubeEmbedSrc || $facebookEmbedSrc)
<section class="theme-surface-muted border-b border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
        <div class="mb-10">
            <h2 class="section-heading font-serif text-2xl font-bold text-[var(--accent)] mb-3">Video & Social</h2>
            <p class="text-slate-500 text-sm">Latest updates from YouTube and Facebook.</p>
        </div>
        <div class="grid lg:grid-cols-2 gap-8 lg:items-start">
            @if($youtubeEmbedSrc)
                <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] overflow-hidden h-full">
                    <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between gap-3 shrink-0">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-[var(--accent)]">YouTube</h3>
                        @if($youtubePageUrl ?? null)
                            <a href="{{ $youtubePageUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs text-[var(--secondary)] hover:underline shrink-0">
                                View channel
                            </a>
                        @endif
                    </div>
                    <p class="px-5 py-2 text-xs text-slate-500 border-b border-slate-100 shrink-0">
                        @if($youtubeDailyRotation ?? false)
                            Featured video rotates daily from your channel
                        @else
                            Latest upload from the channel
                        @endif
                        @if($youtubeAutoplay ?? false)
                            · autoplays muted
                        @endif
                    </p>
                    <div class="relative w-full aspect-video bg-slate-100 shrink-0">
                        <iframe
                            src="{{ $youtubeEmbedSrc }}"
                            title="YouTube video"
                            class="absolute inset-0 w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            @if($youtubeAutoplay ?? false) loading="eager" @else loading="lazy" @endif
                            referrerpolicy="strict-origin-when-cross-origin"
                        ></iframe>
                    </div>
                </div>
            @endif

            @if($facebookEmbedSrc)
                <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] overflow-hidden h-full">
                    <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between gap-3 shrink-0">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-[var(--accent)]">Facebook Page</h3>
                        @if($facebookPageUrl ?? null)
                            <a href="{{ $facebookPageUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs text-[var(--secondary)] hover:underline shrink-0">
                                Open page
                            </a>
                        @endif
                    </div>
                    <p class="px-5 py-2 text-xs text-slate-500 border-b border-slate-100 shrink-0">
                        Recent posts and updates from the page timeline.
                    </p>
                    <div class="relative w-full aspect-video bg-slate-100 shrink-0">
                        <iframe
                            src="{{ $facebookEmbedSrc }}"
                            title="Facebook page"
                            class="absolute inset-0 w-full h-full"
                            style="border:none;overflow:hidden"
                            scrolling="no"
                            frameborder="0"
                            allow="encrypted-media"
                            allowfullscreen="true"
                            loading="lazy"
                        ></iframe>
                    </div>
                </div>
            @endif
        </div>
        @if(app()->environment('local') && ($facebookEmbedSrc ?? false))
            <p class="mt-4 text-xs text-slate-500 text-center">
                Facebook embeds may appear blank on localhost. Use “Open page” above, or check on your live domain.
            </p>
        @endif
    </div>
</section>
@endif
