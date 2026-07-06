@extends('layouts.app')

@section('title', 'Biography | '.$profile->name)

@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-8">Biography</h1>
    <div class="prose prose-slate prose-lg max-w-none">
        {!! $profile->bio_html !!}
    </div>

    <div class="mt-10">
        @include('partials.bio-extras')
    </div>

    @if($profile->research_interests)
    <div class="mt-12">
        <h2 class="font-serif text-2xl font-bold text-[var(--accent)] mb-4">Research Interests</h2>
        <ul class="space-y-2">
            @foreach(array_filter(explode("\n", $profile->research_interests)) as $interest)
                <li class="text-slate-600 flex items-start gap-2"><span class="text-[var(--secondary)]">&#9679;</span>{{ trim($interest) }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    @if($profile->hasCv())
        <div class="mt-12 pt-8 border-t border-slate-200">
            @include('partials.cv-link')
        </div>
    @endif
</section>
@endsection
