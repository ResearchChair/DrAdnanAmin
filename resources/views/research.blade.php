@extends('layouts.app')

@section('title', 'Research Activities | '.$profile->name)

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-8">Research Activities</h1>

    @foreach($activities as $type => $items)
        <div class="mb-12">
            <h2 class="font-serif text-2xl font-bold text-[var(--accent)] mb-4">{{ config('academic.activity_types.'.$type, $type) }}</h2>
            <div class="space-y-4">
                @foreach($items as $activity)
                    <div class="bg-white rounded-xl border border-slate-200 p-5">
                        <h3 class="font-semibold text-lg">
                            @if($activity->url)
                                <a href="{{ $activity->url }}" target="_blank" rel="noopener" class="hover:text-[var(--secondary)]">{{ $activity->title }}</a>
                            @else
                                {{ $activity->title }}
                            @endif
                        </h3>
                        <p class="text-slate-600 mt-1">{{ $activity->role }} @if($activity->organization) &middot; {{ $activity->organization }}@endif</p>
                        @if($activity->year)
                            <p class="text-sm text-slate-500 mt-1">{{ $activity->year }}@if($activity->year_end) – {{ $activity->year_end }}@endif</p>
                        @endif
                        @if($activity->description)
                            <p class="text-slate-600 mt-2 text-sm">{{ $activity->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</section>
@endsection
