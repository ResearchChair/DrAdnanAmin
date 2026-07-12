@if(($workedWithOrganizations ?? collect())->isNotEmpty())
@php
    $isHero = ($variant ?? '') === 'hero';
@endphp
<div @class([
    $isHero ? 'min-w-0' : 'theme-surface-muted border-b border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)]',
])>
    <div @class([
        $isHero ? '' : 'max-w-6xl mx-auto px-4 sm:px-6 py-10 md:py-12',
    ])>
        <h2 @class([
            'font-serif font-bold mb-4',
            'text-lg text-white' => $isHero,
            'text-xl md:text-2xl text-[var(--accent)]' => ! $isHero,
        ])>Worked With</h2>
        <div @class([
            'grid gap-3',
            'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5' => $isHero,
            'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-5' => ! $isHero,
        ])>
            @foreach($workedWithOrganizations as $org)
                @php
                    $classes = $isHero
                        ? 'group flex flex-col items-center justify-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-3.5 text-center transition hover:bg-white/10 hover:border-white/25'
                        : 'group flex flex-col items-center justify-center gap-3 rounded-xl border border-[color-mix(in_srgb,var(--accent)_10%,#e2e8f0_90%)] bg-[var(--surface,#fff9f5)] px-4 py-5 text-center transition hover:border-[var(--accent)]/30 hover:shadow-sm';
                @endphp
                @if($org->url)
                    <a href="{{ $org->url }}" target="_blank" rel="noopener noreferrer" class="{{ $classes }}">
                @else
                    <div class="{{ $classes }}">
                @endif
                    @if($org->logoUrl())
                        <img
                            src="{{ $org->logoUrl() }}"
                            alt="{{ $org->name }}"
                            @class([
                                'w-auto max-w-full object-contain',
                                'h-9 opacity-95 group-hover:opacity-100' => $isHero,
                                'h-12 opacity-90 group-hover:opacity-100' => ! $isHero,
                            ])
                            loading="lazy"
                        >
                    @else
                        <div @class([
                            'rounded-full flex items-center justify-center',
                            'h-9 w-9 bg-white/10' => $isHero,
                            'h-12 w-12 bg-[var(--accent)]/10' => ! $isHero,
                        ])>
                            <span @class([
                                'font-serif font-bold',
                                'text-base text-white/80' => $isHero,
                                'text-lg text-[var(--accent)]' => ! $isHero,
                            ])>{{ mb_substr($org->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <span @class([
                        'font-semibold leading-snug',
                        'text-[0.6875rem] text-white/80' => $isHero,
                        'text-xs sm:text-sm text-slate-700' => ! $isHero,
                    ])>{{ $org->name }}</span>
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
