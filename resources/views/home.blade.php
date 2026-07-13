@extends('layouts.app')

@section('title', $profile->name.' | Home')

@section('content')
{{-- Hero: formal faculty profile --}}
<section class="hero-pattern text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-16 md:py-24">
        <div class="hero-layout grid lg:grid-cols-[280px_minmax(0,1fr)] gap-8 sm:gap-12 lg:gap-16 items-start">
            {{-- Portrait --}}
            <div class="hero-portrait">
                <div class="hero-portrait-frame">
                    @if($profile->photoUrl())
                        <img src="{{ $profile->photoUrl() }}"
                             alt="{{ $profile->name }}">
                    @else
                        <div class="hero-portrait-fallback bg-white/10 flex items-center justify-center">
                            <span class="font-serif text-5xl sm:text-7xl font-bold text-white/40">{{ substr($profile->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                @if($profile->hasCv() || $profile->photoUrl())
                    <div class="mt-3 flex flex-row flex-wrap gap-2 justify-center lg:justify-start">
                        @if($profile->hasCv())
                            <a href="{{ route('cv.show') }}" class="hero-link-pill">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                {{ $profile->cv_label ?? 'Download CV' }}
                            </a>
                        @endif
                        @if($profile->photoUrl())
                            <a href="{{ route('photo.download') }}" class="hero-link-pill">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download Photo
                            </a>
                        @endif
                    </div>
                @endif
                @if($profile->email || $profile->phone || $profile->whatsapp || $profile->location)
                    <div class="mt-5 space-y-1.5 text-center lg:text-left min-w-0">
                        @if($profile->email)
                            <p class="text-sm text-white/75 min-w-0">
                                <a href="mailto:{{ $profile->email }}" class="inline-flex items-start gap-2 hover:text-white transition-colors max-w-full">
                                    <svg class="w-3.5 h-3.5 opacity-60 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span class="break-anywhere text-left">{{ $profile->email }}</span>
                                </a>
                            </p>
                        @endif
                        @if($profile->phone)
                            <p class="text-sm text-white/75">
                                <a href="tel:{{ preg_replace('/\s+/', '', $profile->phone) }}" class="inline-flex items-center gap-2 hover:text-white transition-colors">
                                    <svg class="w-3.5 h-3.5 opacity-60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $profile->phone }}
                                </a>
                            </p>
                        @endif
                        @if($profile->whatsappUrl())
                            <p class="text-sm text-white/75">
                                <a href="{{ $profile->whatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 hover:text-white transition-colors">
                                    <svg class="w-3.5 h-3.5 opacity-60 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    {{ $profile->whatsapp }}
                                </a>
                            </p>
                        @endif
                        @if($profile->location)
                            <p class="text-sm text-white/50 inline-flex items-center gap-2 justify-center lg:justify-start">
                                <svg class="w-3.5 h-3.5 opacity-60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $profile->location }}
                            </p>
                        @endif
                    </div>
                @endif

                @include('partials.social-connect', ['variant' => 'hero', 'placement' => 'sidebar'])
            </div>

            {{-- Identity --}}
            <div class="text-center lg:text-left min-w-0">
                <h1 class="font-serif text-2xl sm:text-4xl lg:text-[2.85rem] lg:leading-[1.15] font-bold tracking-tight break-anywhere">
                    {{ $profile->name }}@if($profile->credentials)<span class="font-normal text-white/90">, {{ $profile->credentials }}</span>@endif
                </h1>
                @if($profile->title)
                    <p class="mt-4 sm:mt-5 text-base sm:text-xl text-white/92 font-medium leading-snug max-w-2xl mx-auto lg:mx-0">{{ $profile->title }}</p>
                @endif
                @if($profile->affiliation)
                    <p class="mt-3 sm:mt-4 text-sm sm:text-base text-white/78 leading-relaxed max-w-2xl mx-auto lg:mx-0">{{ $profile->affiliation }}</p>
                @endif
                @if($profile->secondary_affiliation)
                    <p class="mt-2 text-sm text-white/55 italic max-w-2xl mx-auto lg:mx-0">{{ $profile->secondary_affiliation }}</p>
                @endif
                @if($profile->tagline)
                    <blockquote class="mt-6 sm:mt-8 text-sm sm:text-base text-white/65 leading-relaxed border-l-[3px] border-[var(--secondary)] pl-4 sm:pl-5 max-w-xl mx-auto lg:mx-0 text-left">
                        {{ $profile->tagline }}
                    </blockquote>
                @endif

                @include('partials.academic-profiles', ['variant' => 'hero', 'placement' => 'identity'])

                @if($stats)
                    <div class="mt-5 sm:mt-6 hero-stat-panel overflow-hidden">
                        <div class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 lg:divide-x divide-white/10">
                            <div class="px-3 sm:px-4 py-3 sm:py-4 text-center min-w-0">
                                <div class="font-serif text-xl sm:text-3xl font-bold tabular-nums">{{ number_format($stats->publication_count) }}+</div>
                                <div class="mt-1 text-[0.6rem] sm:text-[0.625rem] font-semibold uppercase tracking-[0.14em] sm:tracking-[0.18em] text-white/50">Publications</div>
                            </div>
                            <div class="px-3 sm:px-4 py-3 sm:py-4 text-center min-w-0">
                                <div class="font-serif text-xl sm:text-3xl font-bold tabular-nums">{{ number_format($stats->total_citations) }}+</div>
                                <div class="mt-1 text-[0.6rem] sm:text-[0.625rem] font-semibold uppercase tracking-[0.14em] sm:tracking-[0.18em] text-white/50">Citations</div>
                            </div>
                            <div class="px-3 sm:px-4 py-3 sm:py-4 text-center min-w-0">
                                <div class="font-serif text-xl sm:text-3xl font-bold tabular-nums">{{ $stats->h_index }}</div>
                                <div class="mt-1 text-[0.6rem] sm:text-[0.625rem] font-semibold uppercase tracking-[0.14em] sm:tracking-[0.18em] text-white/50">h-index</div>
                            </div>
                            <div class="px-3 sm:px-4 py-3 sm:py-4 text-center min-w-0">
                                <div class="font-serif text-xl sm:text-3xl font-bold tabular-nums">{{ $stats->i10_index }}</div>
                                <div class="mt-1 text-[0.6rem] sm:text-[0.625rem] font-semibold uppercase tracking-[0.14em] sm:tracking-[0.18em] text-white/50">i10-index</div>
                            </div>
                        </div>
                    </div>
                @endif

                @include('partials.worked-with', ['variant' => 'hero'])
            </div>
        </div>
    </div>
</section>

{{-- Biography, publications sidebar, products --}}
@include('partials.home-content')
@endsection
