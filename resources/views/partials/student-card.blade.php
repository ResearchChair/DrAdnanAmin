<div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex flex-wrap items-center gap-2 mb-2">
        <span class="text-xs font-semibold uppercase tracking-wide bg-[var(--secondary)]/10 text-[var(--secondary)] px-2 py-0.5 rounded">{{ $student->status_label }}</span>
        @if($student->degree)
            <span class="text-sm text-slate-500">{{ $student->degree }}</span>
        @endif
    </div>
    <h3 class="font-semibold text-lg">{{ $student->name }}</h3>
    <p class="text-slate-600 mt-1">{{ $student->thesis_title }}</p>
    @if($student->co_supervisors)
        <p class="text-sm text-slate-500 mt-2">Co-supervisors: {{ $student->co_supervisors }}</p>
    @endif
    <p class="text-sm text-slate-400 mt-1">
        @if($student->status === 'completed' && $student->completion_year)
            Completed {{ $student->completion_year }}
        @elseif($student->start_year)
            Started {{ $student->start_year }}
        @endif
    </p>
</div>
