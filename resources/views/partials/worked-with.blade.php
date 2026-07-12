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
        ])>Worked With</h3>
        <div @class([
            'flex flex-wrap',
            'gap-x-5 gap-y-4 justify-center lg:justify-start' => $isHero,
            'gap-x-8 gap-y-6' => ! $isHero,
        ])>
            @foreach($workedWithOrganizations as $org)
                @php
                    $classes = $isHero
                        ? 'group inline-flex flex-col items-center gap-1.5 max-w-[6.5rem] text-center'
                        : 'group inline-flex flex-col items-center gap-2 max-w-[7.5rem] text-center';
                @endphp
                @if($org->url)
                    <a href="{{ $org->url }}" target="_blank" rel="noopener noreferrer" class="{{ $classes }}" title="{{ $org->name }}">
                @else
                    <div class="{{ $classes }}" title="{{ $org->name }}">
                @endif
                    @if($org->logoUrl())
                        <img
                            src="{{ $org->logoUrl() }}"
                            alt="{{ $org->name }}"
                            @class([
                                'w-auto max-w-full object-contain',
                                'h-8 opacity-90 group-hover:opacity-100' => $isHero,
                                'h-11 opacity-90 group-hover:opacity-100' => ! $isHero,
                            ])
                            loading="lazy"
                        >
                    @else
                        <span @class([
                            'font-serif font-bold leading-none',
                            'text-lg text-white/75' => $isHero,
                            'text-xl text-[var(--accent)]' => ! $isHero,
                        ])>{{ mb_substr($org->name, 0, 1) }}</span>
                    @endif
                    @if($org->show_title)
                        <span @class([
                            'font-medium leading-snug',
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
