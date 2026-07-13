@if($academicProfiles->isNotEmpty())
@php
    $isHero = ($variant ?? '') === 'hero';
    $isIdentity = ($placement ?? '') === 'identity';
@endphp
@if($isHero && $isIdentity)
<style>
    .hero-link-pill--compact {
        gap: 0.35rem;
        padding: 0.375rem 0.625rem;
        font-size: 0.75rem;
        white-space: normal;
    }
</style>
@endif
<div @class([
    $isHero && $isIdentity ? 'mt-6 sm:mt-8 pt-5 sm:pt-6 border-t border-white/15 min-w-0' : '',
    $isHero && ! $isIdentity ? 'min-w-0' : '',
    ! $isHero ? 'mt-10 pt-8 border-t border-slate-200 min-w-0' : '',
])>
    <h3 @class([
        'text-xs font-semibold uppercase tracking-[0.2em] mb-4',
        'text-white/70 text-center lg:text-left' => $isHero && $isIdentity,
        'text-white/70' => $isHero && ! $isIdentity,
        'text-slate-500' => ! $isHero,
    ])>Academic Profiles</h3>
    <div @class([
        'flex gap-1.5',
        'flex-wrap justify-center lg:justify-start' => $isHero && $isIdentity,
        'flex-wrap justify-center lg:justify-start' => $isHero && ! $isIdentity,
        'flex-wrap justify-center' => ! $isHero,
    ])>
        @foreach($academicProfiles as $link)
            <a href="{{ $link->url }}"
               target="_blank"
               rel="noopener noreferrer"
               @class([
                   $isHero && $isIdentity ? 'hero-link-pill hero-link-pill--compact' : ($isHero ? 'hero-link-pill' : 'inline-flex items-center gap-2 text-sm font-medium px-3 sm:px-4 py-2 bg-slate-100 hover:bg-slate-200 text-[var(--accent)] border border-slate-200 transition-colors'),
               ])
               title="{{ $link->platform_label }}">
                @include('partials.platform-icon', ['platform' => $link->platform, 'class' => ($isHero && $isIdentity ? 'w-3.5 h-3.5' : 'w-4 h-4').' shrink-0 opacity-80'])
                <span>{{ $link->label ?: $link->platform_label }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif
