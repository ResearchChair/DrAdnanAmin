<article class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] overflow-hidden">
    <div class="flex flex-col sm:flex-row">
        <div class="shrink-0 border-b sm:border-b-0 sm:border-r border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] bg-[color-mix(in_srgb,var(--accent)_6%,#fff_94%)]">
            <div class="student-photo-frame">
                @if($student->photoUrl())
                    <img
                        src="{{ $student->photoUrl() }}"
                        alt="{{ $student->name }}"
                        width="192"
                        height="192"
                    >
                @else
                    <div class="absolute inset-0 flex items-center justify-center font-serif text-4xl font-bold text-[var(--accent)] bg-[var(--accent)]/10">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="min-w-0 flex-1 p-5 sm:p-6">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs font-semibold uppercase tracking-wide px-2 py-0.5 {{ $student->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-[var(--secondary)]/10 text-[var(--secondary)]' }}">
                    {{ $student->status_label }}
                </span>
                @if($student->degree)
                    <span class="text-xs text-slate-500">{{ $student->degree }}</span>
                @endif
                @if($student->batch)
                    <span class="text-xs text-slate-500">&middot; Batch {{ $student->batch }}</span>
                @endif
            </div>

            <h2 class="font-serif text-2xl font-bold text-[var(--accent)]">{{ $student->name }}</h2>

            <p class="mt-2 text-lg font-medium text-slate-800 leading-snug">{{ $student->thesis_title }}</p>

            @if($student->description)
                <button
                    type="button"
                    @click="openAbstract({
                        name: @js($student->name),
                        title: @js($student->thesis_title),
                        abstract: @js($student->description),
                    })"
                    class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-[var(--secondary)] hover:text-[var(--accent)] transition-colors group"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="group-hover:underline">Read abstract</span>
                </button>
            @endif

            @if($student->co_supervisors)
                <p class="text-sm text-slate-500 mt-3">Co-supervisors: {{ $student->co_supervisors }}</p>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500">
                @if($student->status === 'in_progress' && $student->start_year)
                    <span>Started {{ $student->start_year }}</span>
                @endif
                @if($student->completedDateLabel())
                    <span>Completed {{ $student->completedDateLabel() }}</span>
                @endif
            </div>

            @if($student->publication && $student->publication->primaryUrl())
                <div class="mt-4 pt-4 border-t border-slate-200/80">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Published article</p>
                    <a
                        href="{{ $student->publication->primaryUrl() }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-start gap-2 text-sm font-medium text-[var(--secondary)] hover:text-[var(--accent)] transition-colors group"
                    >
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span class="group-hover:underline">{{ $student->publication->title }}</span>
                    </a>
                    @if($student->publication->venue || $student->publication->year)
                        <p class="text-xs text-slate-500 mt-1">
                            @if($student->publication->venue){{ $student->publication->venue }}@endif
                            @if($student->publication->venue && $student->publication->year) &middot; @endif
                            @if($student->publication->year){{ $student->publication->year }}@endif
                        </p>
                    @endif
                </div>
            @endif

            @if($student->profileLinksList() !== [])
                <div class="mt-4 pt-4 border-t border-slate-200/80">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Student profiles</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($student->profileLinksList() as $link)
                            <a
                                href="{{ $link['url'] }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[var(--accent)] border border-[color-mix(in_srgb,var(--accent)_15%,#fff_85%)] bg-white hover:bg-[var(--accent)]/5 transition-colors"
                            >
                                @include('partials.platform-icon', ['platform' => $link['platform'], 'class' => 'w-3.5 h-3.5'])
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</article>
