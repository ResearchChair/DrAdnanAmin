@extends('layouts.app')

@section('title', 'Research Students | '.$profile->name)

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16" x-data="{ tab: 'in_progress' }">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-8">Research Students</h1>

    <div class="flex gap-4 mb-8 border-b border-slate-200">
        <button @click="tab = 'in_progress'" :class="tab === 'in_progress' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-slate-500'" class="pb-3 border-b-2 font-medium">In Progress ({{ $inProgress->count() }})</button>
        <button @click="tab = 'completed'" :class="tab === 'completed' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-slate-500'" class="pb-3 border-b-2 font-medium">Completed ({{ $completed->count() }})</button>
    </div>

    <div x-show="tab === 'in_progress'" class="space-y-4">
        @forelse($inProgress as $student)
            @include('partials.student-card', ['student' => $student])
        @empty
            <p class="text-slate-500">No students currently in progress.</p>
        @endforelse
    </div>

    <div x-show="tab === 'completed'" x-cloak class="space-y-4">
        @forelse($completed as $student)
            @include('partials.student-card', ['student' => $student])
        @empty
            <p class="text-slate-500">No completed students yet.</p>
        @endforelse
    </div>
</section>
@endsection
