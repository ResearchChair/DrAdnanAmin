@extends('layouts.app')

@section('title', 'Gallery | '.$profile->name)

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16" x-data="{ lightbox: null }">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-8">Photo Gallery</h1>

    @foreach($albums as $album)
        <div class="mb-12">
            <h2 class="font-serif text-2xl font-bold text-[var(--accent)] mb-2">{{ $album->title }}</h2>
            @if($album->description)
                <p class="text-slate-600 mb-6">{{ $album->description }}</p>
            @endif
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($album->images as $image)
                    <button @click="lightbox = '{{ asset('storage/'.$image->image_path) }}'" class="group relative aspect-square rounded-xl overflow-hidden bg-slate-200">
                        @if($image->image_path && file_exists(public_path('storage/'.$image->image_path)))
                            <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->title ?? $image->caption }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm">No image</div>
                        @endif
                        @if($image->caption)
                            <div class="absolute inset-x-0 bottom-0 bg-black/50 text-white text-xs p-2 opacity-0 group-hover:opacity-100 transition-opacity">{{ $image->caption }}</div>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach

    <div x-show="lightbox" x-cloak @click="lightbox = null" @keydown.escape.window="lightbox = null" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
        <img :src="lightbox" class="max-w-full max-h-full rounded-lg shadow-2xl" alt="Gallery image">
    </div>
</section>
@endsection
