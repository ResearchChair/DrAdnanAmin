@if($earnedBadges->isNotEmpty())
<div class="mt-12 pt-10 border-t border-slate-200">
    <div class="mb-6">
        <h2 class="section-heading font-serif text-2xl font-bold text-[var(--accent)] mb-2">Earned Badges & Certificates</h2>
        <p class="text-sm text-slate-500 max-w-2xl">Professional certifications and credentials.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($earnedBadges as $badge)
            @if($badge->url)
                <a
                    href="{{ $badge->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group flex items-center gap-4 p-4 theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] hover:border-[var(--accent)]/25 hover:shadow-sm transition-all duration-200"
                >
                    @include('partials.earned-badge-card-body', ['badge' => $badge])
                </a>
            @else
                <div class="flex items-center gap-4 p-4 theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)]">
                    @include('partials.earned-badge-card-body', ['badge' => $badge])
                </div>
            @endif
        @endforeach
    </div>
</div>
@endif
