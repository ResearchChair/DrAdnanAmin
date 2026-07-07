@extends('layouts.app')

@section('title', 'Research Scholars | '.$profile->name)

@section('content')
<section
    class="max-w-6xl mx-auto px-4 sm:px-6 py-12"
    x-data="{
        tab: @js($defaultStudentTab),
        modal: null,
        openAbstract(data) {
            this.modal = data;
        },
        closeModal() {
            this.modal = null;
        }
    }"
    @keydown.escape.window="closeModal()"
>
    <h1 class="font-serif text-3xl font-bold text-[var(--accent)] mb-2">Research Scholars</h1>
    <p class="text-sm text-slate-600 mb-5 max-w-3xl">Supervised research scholars and graduates, grouped by category.</p>

    <div class="flex flex-wrap gap-2 sm:gap-4 mb-5 border-b border-slate-200">
        @foreach($studentStatuses as $status => $label)
            <button
                type="button"
                @click="tab = @js($status)"
                :class="tab === @js($status) ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="pb-2.5 border-b-2 text-sm font-medium transition-colors"
            >
                {{ $label }} ({{ $studentsByStatus[$status]->count() }})
            </button>
        @endforeach
    </div>

    @foreach($studentStatuses as $status => $label)
        <div x-show="tab === @js($status)" x-cloak class="space-y-3">
            @forelse($studentsByStatus[$status] as $student)
                @include('partials.student-card', ['student' => $student, 'hideStatusBadge' => true])
            @empty
                <p class="text-sm text-slate-500">No {{ strtolower($label) }} scholars yet.</p>
            @endforelse
        </div>
    @endforeach

    {{-- Abstract modal --}}
    <div
        x-show="modal"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
        @click.self="closeModal()"
    >
        <div
            x-show="modal"
            x-transition
            class="theme-surface w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl border border-[color-mix(in_srgb,var(--accent)_15%,#fff_85%)]"
            @click.stop
        >
            <div class="flex items-start justify-between gap-4 p-5 border-b border-slate-200 shrink-0">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[var(--secondary)]" x-text="modal?.name"></p>
                    <h2 class="font-serif text-xl font-bold text-[var(--accent)] mt-1" x-text="modal?.title"></h2>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mt-3">Abstract</p>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 text-2xl leading-none shrink-0" aria-label="Close">&times;</button>
            </div>
            <div class="p-5 overflow-y-auto flex-1">
                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line" x-text="modal?.abstract"></p>
            </div>
            <div class="flex items-center justify-end p-4 border-t border-slate-200 shrink-0 bg-slate-50/50">
                <button
                    type="button"
                    @click="closeModal()"
                    class="px-4 py-2 text-sm font-medium text-white bg-[var(--accent)] hover:opacity-90 transition-opacity"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</section>
@endsection
