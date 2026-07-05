@extends('layouts.app')

@section('title', 'Gallery | '.$profile->name)

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16" x-data="{ lightbox: null }">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-8">Photo Gallery</h1>

    @forelse($albums as $album)
        <div class="mb-12">
            <h2 class="font-serif text-2xl font-bold text-[var(--accent)] mb-2">{{ $album->title }}</h2>
            @if($album->description)
                <p class="text-slate-600 mb-6">{{ $album->description }}</p>
            @endif
            <div class="gallery-grid gallery-grid--album">
                @foreach($album->images as $image)
                    @if($image->imageUrl())
                        <button
                            type="button"
                            @click="lightbox = '{{ $image->imageUrl() }}'"
                            class="gallery-cell gallery-cell--square group text-left"
                        >
                            <img
                                src="{{ $image->imageUrl() }}"
                                alt="{{ $image->title ?? $image->caption ?? 'Gallery image' }}"
                                loading="lazy"
                            >
                            @if($image->caption)
                                <div class="absolute inset-x-0 bottom-0 bg-black/50 text-white text-xs p-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    {{ $image->caption }}
                                </div>
                            @endif
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-slate-500 italic">No gallery albums yet.</p>
    @endforelse

    <div
        x-show="lightbox"
        x-cloak
        @click="lightbox = null"
        @keydown.escape.window="lightbox = null"
        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4"
    >
        <img :src="lightbox" class="max-w-full max-h-[90vh] w-auto h-auto object-contain shadow-2xl" alt="Gallery image">
    </div>
</section>
@endsection
