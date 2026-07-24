@extends('layouts.app')

@section('title', 'Co-authored Publications | '.$profile->name)

@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-12">
    <div class="mb-6">
        <h1 class="font-serif text-3xl font-bold text-[var(--accent)] mb-2">Co-authored Publications</h1>
        <p class="text-sm text-slate-600">
            Showing publications shared for <strong>{{ $collaboratorEmail }}</strong>.
        </p>
    </div>

    @if($coauthorPublications->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
            No publications matched this collaborator link.
        </div>
    @else
        <div class="space-y-4">
            @foreach($coauthorPublications as $publication)
                @include('partials.publication-card', ['publication' => $publication])
            @endforeach
        </div>
    @endif
</section>
@endsection
