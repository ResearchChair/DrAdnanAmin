@extends('layouts.app')

@section('title', 'Training & Facilitation | '.$profile->name)

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-8">Training & Facilitation</h1>
    <div class="space-y-4">
        @forelse($sessions as $session)
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide bg-[var(--accent)]/10 text-[var(--accent)] px-2 py-0.5 rounded">{{ $session->type_label }}</span>
                    <span class="text-xs text-slate-500">{{ $session->role }}</span>
                    @if($session->year)<span class="text-sm text-slate-400">{{ $session->year }}</span>@endif
                </div>
                <h3 class="font-semibold text-lg">{{ $session->title }}</h3>
                @if($session->event_name)
                    <p class="text-slate-600 mt-1">{{ $session->event_name }} @if($session->organization)&middot; {{ $session->organization }}@endif</p>
                @endif
                @if($session->description)
                    <p class="text-sm text-slate-500 mt-2">{{ $session->description }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-4">
                    @if($session->materials_url)
                        <a href="{{ $session->materials_url }}" target="_blank" rel="noopener" class="text-sm text-[var(--secondary)] hover:underline">Download Materials</a>
                    @endif
                    @if($session->galleryAlbum)
                        <a href="{{ route('gallery') }}#album-{{ $session->galleryAlbum->id }}" class="text-sm text-[var(--secondary)] hover:underline">View gallery album</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-slate-500">No training sessions recorded yet.</p>
        @endforelse
    </div>
</section>
@endsection
