@if(($workedWithOrganizations ?? collect())->isNotEmpty())
@php
    $isHero = ($variant ?? '') === 'hero';
@endphp
<div @class([
    $isHero ? 'min-w-0 mt-5 pt-5 border-t border-white/15' : 'theme-surface-muted border-b border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)]',
])>
    <div @class([
        $isHero ? '' : 'max-w-6xl mx-auto px-4 sm:px-6 py-10 md:py-12',
    ])>
        <h3 @class([
            'text-xs font-semibold uppercase tracking-[0.2em] mb-4',
            'text-white/70 text-center lg:text-left' => $isHero,
            'text-slate-500' => ! $isHero,
        ])>Work Connections</h3>
        <div @class([
            'work-connections',
            'work-connections--hero flex flex-wrap gap-x-4 gap-y-4 justify-center lg:justify-start' => $isHero,
            'work-connections--page flex flex-wrap gap-x-6 gap-y-6' => ! $isHero,
        ])>
            @foreach($workedWithOrganizations as $org)
                @php
                    $classes = $isHero
                        ? 'work-connection group'
                        : 'work-connection work-connection--page group';
                @endphp
                @if($org->url)
                    <a href="{{ $org->url }}" target="_blank" rel="noopener noreferrer" class="{{ $classes }}" title="{{ $org->name }}">
                @else
                    <div class="{{ $classes }}" title="{{ $org->name }}">
                @endif
                    <span class="work-connection__logo">
                        @if($org->logoUrl())
                            <img
                                src="{{ $org->logoUrl() }}"
                                alt="{{ $org->name }}"
                                loading="lazy"
                            >
                        @else
                            <span @class([
                                'font-serif font-bold leading-none',
                                'text-lg text-white/75' => $isHero,
                                'text-xl text-[var(--accent)]' => ! $isHero,
                            ])>{{ mb_substr($org->name, 0, 1) }}</span>
                        @endif
                    </span>
                    @if($org->show_title)
                        <span @class([
                            'work-connection__label font-medium leading-snug',
                            'text-[0.625rem] text-white/70 group-hover:text-white/90' => $isHero,
                            'text-xs text-slate-600' => ! $isHero,
                        ])>{{ $org->name }}</span>
                    @endif
                @if($org->url)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif
