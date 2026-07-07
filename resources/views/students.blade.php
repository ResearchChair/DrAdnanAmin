@extends('layouts.app')

@section('title', 'Research Scholars | '.$profile->name)

@section('content')
<style>
    .scholar-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        padding: 0.375rem;
        margin-bottom: 1.25rem;
        border-radius: 0.875rem;
        background: color-mix(in srgb, var(--accent) 5%, var(--surface-muted, #f5ebe8) 95%);
        border: 1px solid color-mix(in srgb, var(--accent) 12%, #fff 88%);
    }
    .scholar-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5625rem 0.875rem;
        border-radius: 0.625rem;
        border: 1px solid transparent;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        background: transparent;
        cursor: pointer;
        transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .scholar-tab:hover:not(.scholar-tab--active) {
        color: var(--accent);
        background: color-mix(in srgb, var(--accent) 7%, #fff 93%);
    }
    .scholar-tab--active {
        color: var(--accent);
        background: var(--surface, #fff9f5);
        border-color: color-mix(in srgb, var(--accent) 18%, #fff 82%);
        box-shadow: 0 1px 2px color-mix(in srgb, var(--accent) 10%, transparent 90%), 0 4px 12px color-mix(in srgb, var(--accent) 8%, transparent 92%);
    }
    .scholar-tab__label { white-space: nowrap; }
    .scholar-tab__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.5rem;
        height: 1.5rem;
        padding: 0 0.4375rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: #64748b;
        background: color-mix(in srgb, var(--accent) 8%, #fff 92%);
    }
    .scholar-tab--active .scholar-tab__count { color: #fff; background: var(--accent); }
    .scholar-tab--active.scholar-tab--completed .scholar-tab__count { background: #059669; }
    .scholar-tab--active.scholar-tab--completed { border-color: color-mix(in srgb, #059669 35%, #fff 65%); }
    .scholar-tab--active.scholar-tab--in-progress .scholar-tab__count { background: var(--secondary); }
    .scholar-tab--active.scholar-tab--in-progress { border-color: color-mix(in srgb, var(--secondary) 40%, #fff 60%); }
    .scholar-tab--active.scholar-tab--guest .scholar-tab__count { background: #2563eb; }
    .scholar-tab--active.scholar-tab--guest { border-color: color-mix(in srgb, #2563eb 35%, #fff 65%); }
    .scholar-tab--active.scholar-tab--fyp .scholar-tab__count { background: #d97706; }
    .scholar-tab--active.scholar-tab--fyp { border-color: color-mix(in srgb, #d97706 35%, #fff 65%); }
</style>
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

    @php
        $scholarTabStyles = [
            'completed' => 'scholar-tab--completed',
            'in_progress' => 'scholar-tab--in-progress',
            'guest_scholar' => 'scholar-tab--guest',
            'fyp_projects' => 'scholar-tab--fyp',
        ];
    @endphp

    <div class="scholar-tabs" role="tablist" aria-label="Scholar categories">
        @foreach($studentStatuses as $status => $label)
            <button
                type="button"
                role="tab"
                :aria-selected="tab === @js($status)"
                @click="tab = @js($status)"
                class="scholar-tab {{ $scholarTabStyles[$status] ?? '' }}"
                :class="{ 'scholar-tab--active': tab === @js($status) }"
            >
                <span class="scholar-tab__label">{{ $label }}</span>
                <span class="scholar-tab__count">{{ $studentsByStatus[$status]->count() }}</span>
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
