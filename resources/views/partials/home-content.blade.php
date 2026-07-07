{{-- Biography & research --}}
<section class="theme-surface border-b border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
        <div class="grid lg:grid-cols-5 gap-12 lg:gap-16">
            {{-- Full biography --}}
            <div class="lg:col-span-3">
                <h2 class="section-heading font-serif text-2xl font-bold text-[var(--accent)] mb-6">Biographical Sketch</h2>
                <div class="prose-academic prose-academic-rich text-base">
                    {!! $profile->bio_html !!}
                </div>

                @include('partials.earned-badges')

                @include('partials.gallery-featured')
            </div>

            {{-- Sidebar: interests, bio extras, publications --}}
            <div class="lg:col-span-2">
                <h2 class="section-heading font-serif text-2xl font-bold text-[var(--accent)] mb-6">Research Interests</h2>
                <ul class="space-y-4">
                    @forelse(array_filter(explode("\n", $profile->research_interests ?? '')) as $interest)
                        <li class="interest-item text-slate-600 text-[0.95rem] leading-relaxed">
                            {{ trim($interest) }}
                        </li>
                    @empty
                        <li class="text-slate-400 text-sm italic">Research interests will appear here.</li>
                    @endforelse
                </ul>

                <div class="mt-10 pt-8 border-t border-slate-200">
                    @include('partials.bio-extras')
                </div>

                <div class="mt-10 pt-8 border-t border-slate-200">
                    <div class="flex items-end justify-between mb-5">
                        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Recent Publications</h3>
                        <a href="{{ route('publications') }}" class="text-xs text-[var(--secondary)] hover:underline">View all</a>
                    </div>
                    <ol class="space-y-5">
                        @forelse($recentPublications as $publication)
                            <li class="pb-5 border-b border-slate-100 last:border-0 last:pb-0">
                                @include('partials.publication-compact', ['publication' => $publication])
                            </li>
                        @empty
                            <li class="text-slate-400 text-sm italic">No publications yet.</li>
                        @endforelse
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Products & initiatives --}}
@if($showcaseProducts->isNotEmpty())
<section class="theme-surface-muted border-b border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
        <div class="mb-10">
            <h2 class="section-heading font-serif text-2xl font-bold text-[var(--accent)] mb-3">Products & Initiatives</h2>
            <p class="text-slate-500 text-sm max-w-2xl">Digital platforms and research initiatives developed and led in academic and industry contexts.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($showcaseProducts as $product)
                <article class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-6 flex flex-col hover:shadow-md hover:border-[var(--accent)]/25 transition-all duration-200">
                    @if($product->logo_path)
                        <img src="{{ asset('storage/'.$product->logo_path) }}" alt="{{ $product->name }}" class="h-12 w-auto mb-4 object-contain object-left">
                    @else
                        <div class="w-12 h-12 mb-4 bg-[var(--accent)]/10 flex items-center justify-center">
                            <span class="font-serif text-lg font-bold text-[var(--accent)]">{{ substr($product->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <h3 class="font-serif text-xl font-bold text-[var(--accent)]">{{ $product->name }}</h3>
                    @if($product->tagline)
                        <p class="text-xs font-semibold uppercase tracking-wider text-[var(--secondary)] mt-1">{{ $product->tagline }}</p>
                    @endif
                    @if($product->description)
                        <p class="text-sm text-slate-600 mt-3 leading-relaxed flex-1">{{ $product->description }}</p>
                    @endif
                    @if($product->url)
                        <a href="{{ $product->url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 mt-5 text-sm font-semibold text-[var(--accent)] hover:text-[var(--secondary)] transition-colors group">
                            Visit platform
                            <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@include('partials.social-embeds')
