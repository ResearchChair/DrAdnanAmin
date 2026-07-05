@if($profile->hasCv())
    <a
        href="{{ route('cv.show') }}"
        class="inline-flex items-center gap-2 text-sm font-semibold text-[var(--accent)] hover:text-[var(--secondary)] transition-colors {{ $class ?? '' }}"
    >
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        {{ $profile->cv_label ?? 'Download CV' }}
        @if(\App\Support\CvAccess::requiresKey())
            <span class="text-xs font-normal text-slate-500">(key required)</span>
        @endif
    </a>
@endif
