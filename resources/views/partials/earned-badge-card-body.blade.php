<div class="shrink-0 w-14 h-14 flex items-center justify-center bg-white border border-slate-200/80 p-1.5">
    @if($badge->logoUrl())
        <img
            src="{{ $badge->logoUrl() }}"
            alt="{{ $badge->title }}"
            class="max-w-full max-h-full object-contain"
            width="56"
            height="56"
        >
    @else
        <span class="font-serif text-lg font-bold text-[var(--accent)]">{{ strtoupper(substr($badge->title, 0, 1)) }}</span>
    @endif
</div>
<div class="min-w-0">
    <p class="text-sm font-semibold text-slate-800 leading-snug {{ $badge->url ? 'group-hover:text-[var(--accent)] transition-colors' : '' }}">{{ $badge->title }}</p>
    @if($badge->issuer || $badge->year)
        <p class="text-xs text-slate-500 mt-1">
            @if($badge->issuer){{ $badge->issuer }}@endif
            @if($badge->issuer && $badge->year) &middot; @endif
            @if($badge->year){{ $badge->year }}@endif
        </p>
    @endif
</div>
