@if($featuredGalleryImages->isNotEmpty())
<section class="theme-surface-muted border-b border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)]" x-data="{ lightbox: null }">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 md:py-16">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="section-heading font-serif text-2xl font-bold text-[var(--accent)] mb-2">Gallery</h2>
                <p class="text-slate-500 text-sm">Photos from conferences, teaching, and research activities.</p>
            </div>
            <a href="{{ route('gallery') }}" class="text-sm text-[var(--secondary)] hover:underline shrink-0">View all</a>
        </div>
        <div class="gallery-grid gallery-grid--featured">
            @foreach($featuredGalleryImages as $image)
                @if($image->imageUrl())
                    <button
                        type="button"
                        @click="lightbox = '{{ $image->imageUrl() }}'"
                        class="gallery-cell gallery-cell--tall group text-left"
                    >
                        <img
                            src="{{ $image->imageUrl() }}"
                            alt="{{ $image->title ?? $image->caption ?? 'Gallery image' }}"
                            loading="lazy"
                        >
                        @if($image->caption)
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent text-white text-xs leading-snug p-3 pt-8 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                {{ $image->caption }}
                            </div>
                        @endif
                    </button>
                @endif
            @endforeach
        </div>
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
</section>
@endif
