@if(($workedWithOrganizations ?? collect())->isNotEmpty())
<section class="theme-surface-muted border-b border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 md:py-12">
        <div class="mb-6 md:mb-8 text-center md:text-left">
            <h2 class="font-serif text-xl md:text-2xl font-bold text-[var(--accent)]">Worked With</h2>
            <p class="mt-1 text-sm text-slate-500">Organizations and institutions collaborated with over the years.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-5">
            @foreach($workedWithOrganizations as $org)
                @php
                    $classes = 'group flex flex-col items-center justify-center gap-3 rounded-xl border border-[color-mix(in_srgb,var(--accent)_10%,#e2e8f0_90%)] bg-[var(--surface,#fff9f5)] px-4 py-5 text-center transition hover:border-[var(--accent)]/30 hover:shadow-sm';
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
                            class="h-12 w-auto max-w-full object-contain opacity-90 group-hover:opacity-100"
                            loading="lazy"
                        >
                    @else
                        <div class="h-12 w-12 rounded-full bg-[var(--accent)]/10 flex items-center justify-center">
                            <span class="font-serif text-lg font-bold text-[var(--accent)]">{{ mb_substr($org->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="text-xs sm:text-sm font-semibold text-slate-700 leading-snug">{{ $org->name }}</span>
                @if($org->url)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif
