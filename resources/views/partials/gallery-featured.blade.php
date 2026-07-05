@if($featuredGalleryImages->isNotEmpty())
<div class="mt-10 pt-8 border-t border-slate-200" x-data="{ lightbox: null }">
    <div class="flex items-end justify-between mb-5">
        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Gallery</h3>
        <a href="{{ route('gallery') }}" class="text-xs text-[var(--secondary)] hover:underline">View all</a>
    </div>
    <div class="gallery-grid gallery-grid--compact">
        @foreach($featuredGalleryImages as $image)
            @if($image->imageUrl())
                <button
                    type="button"
                    @click="lightbox = '{{ $image->imageUrl() }}'"
                    class="gallery-cell group text-left"
                >
                    <img
                        src="{{ $image->imageUrl() }}"
                        alt="{{ $image->title ?? $image->caption ?? 'Gallery image' }}"
                        loading="lazy"
                    >
                    @if($image->caption)
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent text-white text-[0.65rem] leading-snug p-2 pt-6 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            {{ $image->caption }}
                        </div>
                    @endif
                </button>
            @endif
        @endforeach
    </div>

    <div
        x-show="lightbox"
        x-cloak
        @click="lightbox = null"
        @keydown.escape.window="lightbox = null"
        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4"
    >
        <img :src="lightbox" class="max-w-full max-h-[90vh] w-auto h-auto object-contain shadow-2xl" alt="Gallery image">
    </div>
</div>
@endif
