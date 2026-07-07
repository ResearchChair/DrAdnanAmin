<article class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] overflow-hidden">
    <div class="flex flex-row items-start">
        <div class="shrink-0 border-r border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] bg-[color-mix(in_srgb,var(--accent)_6%,#fff_94%)]">
            <div class="student-photo-frame">
                @if($student->photoUrl())
                    <img
                        src="{{ $student->photoUrl() }}"
                        alt="{{ $student->name }}"
                        width="88"
                        height="88"
                    >
                @else
                    <div class="absolute inset-0 flex items-center justify-center font-serif text-xl font-bold text-[var(--accent)] bg-[var(--accent)]/10">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="min-w-0 flex-1 p-3 sm:p-4">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-1">
                <h2 class="font-serif text-lg font-bold text-[var(--accent)] leading-tight">{{ $student->name }}</h2>
                @unless($hideStatusBadge ?? false)
                    @php
                        $statusBadgeClass = match ($student->status) {
                            'completed' => 'bg-emerald-50 text-emerald-700',
                            'guest_scholar' => 'bg-blue-50 text-blue-700',
                            'fyp_projects' => 'bg-amber-50 text-amber-800',
                            default => 'bg-[var(--secondary)]/10 text-[var(--secondary)]',
                        };
                    @endphp
                    <span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 {{ $statusBadgeClass }}">
                        {{ $student->status_label }}
                    </span>
                @endunless
            </div>

            <p class="text-sm text-slate-700 leading-snug">{{ $student->thesis_title }}</p>

            <p class="mt-1.5 text-xs text-slate-500">
                @if($student->degree)<span>{{ $student->degree }}</span>@endif
                @if($student->batch)<span>@if($student->degree) &middot; @endif Batch {{ $student->batch }}</span>@endif
                @if($student->status === 'in_progress' && $student->start_year)
                    <span>@if($student->degree || $student->batch) &middot; @endif Started {{ $student->start_year }}</span>
                @endif
                @if($student->completedDateLabel())
                    <span>@if($student->degree || $student->batch) &middot; @endif Completed {{ $student->completedDateLabel() }}</span>
                @endif
            </p>

            @if($student->co_supervisors)
                <p class="text-xs text-slate-500 mt-1">Co-supervisors: {{ $student->co_supervisors }}</p>
            @endif

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                @if($student->description)
                    <button
                        type="button"
                        @click="openAbstract({
                            name: @js($student->name),
                            title: @js($student->thesis_title),
                            abstract: @js($student->description),
                        })"
                        class="inline-flex items-center gap-1 text-xs font-medium text-[var(--secondary)] hover:text-[var(--accent)] transition-colors group"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="group-hover:underline">Abstract</span>
                    </button>
                @endif

                @if($student->publications->isNotEmpty())
                    <span class="text-xs text-slate-400">|</span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">
                            {{ str('Paper')->plural($student->publications->count()) }}
                        </p>
                        <ul class="space-y-0.5">
                            @foreach($student->publications as $publication)
                                <li class="text-xs leading-snug">
                                    @if($publication->primaryUrl())
                                        <a
                                            href="{{ $publication->primaryUrl() }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="text-[var(--secondary)] hover:text-[var(--accent)] hover:underline"
                                        >{{ $publication->title }}</a>
                                    @else
                                        <span class="text-slate-700">{{ $publication->title }}</span>
                                    @endif
                                    @if($publication->venue || $publication->year)
                                        <span class="text-slate-400">
                                            @if($publication->venue) — {{ $publication->venue }}@endif
                                            @if($publication->year) ({{ $publication->year }})@endif
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            @if($student->profileLinksList() !== [])
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    @foreach($student->profileLinksList() as $link)
                        <a
                            href="{{ $link['url'] }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-medium text-[var(--accent)] border border-[color-mix(in_srgb,var(--accent)_15%,#fff_85%)] bg-white hover:bg-[var(--accent)]/5 transition-colors"
                        >
                            @include('partials.platform-icon', ['platform' => $link['platform'], 'class' => 'w-3 h-3'])
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</article>
