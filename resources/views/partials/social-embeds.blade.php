@if($youtubeEmbedSrc || $facebookEmbedSrc)
<section class="theme-surface-muted border-b border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
        <div class="mb-10">
            <h2 class="section-heading font-serif text-2xl font-bold text-[var(--accent)] mb-3">Video & Social</h2>
            <p class="text-slate-500 text-sm">Latest updates from YouTube and Facebook.</p>
        </div>
        <div class="grid lg:grid-cols-2 gap-8">
            @if($youtubeEmbedSrc)
                <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-200 bg-white">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-[var(--accent)]">YouTube Channel</h3>
                    </div>
                    <div class="relative w-full aspect-video bg-slate-100">
                        <iframe
                            src="{{ $youtubeEmbedSrc }}"
                            title="YouTube channel"
                            class="absolute inset-0 w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                    </div>
                </div>
            @endif

            @if($facebookEmbedSrc)
                <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-200 bg-white">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-[var(--accent)]">Facebook Page</h3>
                    </div>
                    <div class="relative w-full min-h-[500px] bg-slate-100">
                        <iframe
                            src="{{ $facebookEmbedSrc }}"
                            title="Facebook page"
                            class="absolute inset-0 w-full h-full min-h-[500px]"
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
    </div>
</section>
@endif
