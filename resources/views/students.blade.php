@extends('layouts.app')

@section('title', 'Research Students | '.$profile->name)

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-3">Research Students</h1>
    <p class="text-slate-600 mb-10 max-w-3xl">Supervised research students and graduates, listed in display order.</p>

    <div class="space-y-6">
        @forelse($students as $student)
            @include('partials.student-card', ['student' => $student])
        @empty
            <p class="text-slate-500">No research students recorded yet.</p>
        @endforelse
    </div>
</section>
@endsection
