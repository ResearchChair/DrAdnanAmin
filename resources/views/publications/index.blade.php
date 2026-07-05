@extends('layouts.app')

@section('title', 'Publications | '.$profile->name)

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16" x-data="{ type: '{{ $currentType }}', search: '{{ $search }}' }">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-8">Publications</h1>

    <form method="GET" class="flex flex-wrap gap-4 mb-8">
        <select name="type" class="rounded-lg border-slate-300 text-sm" onchange="this.form.submit()">
            <option value="">All Types</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}" @selected($currentType === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search publications..." class="rounded-lg border-slate-300 text-sm flex-1 min-w-[200px]">
        <button type="submit" class="bg-[var(--accent)] text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90">Search</button>
    </form>

    <div class="space-y-4">
        @forelse($publications as $publication)
            @include('partials.publication-card', ['publication' => $publication])
        @empty
            <p class="text-slate-500">No publications found.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $publications->links() }}</div>
</section>
@endsection
