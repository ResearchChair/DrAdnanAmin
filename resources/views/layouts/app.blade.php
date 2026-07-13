<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('partials.seo-head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=libre-baskerville:400,700|source-sans-3:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --accent: {{ $accentColor ?? '#5B2C6F' }};
            --secondary: {{ $secondaryColor ?? '#C17AA8' }};
            --surface: {{ $surfaceColor ?? '#FFF9F5' }};
            --surface-muted: {{ $surfaceMutedColor ?? '#F5EBE8' }};
        }
        /* Layout safety (works even before Vite rebuild) */
        html, body { max-width: 100%; overflow-x: hidden; }
        img, video { max-width: 100%; height: auto; }
        iframe { max-width: 100%; }
        .site-brand { max-width: calc(100vw - 4.5rem); }
        .break-anywhere { overflow-wrap: anywhere; word-break: break-word; }
        .hero-portrait { width: 100%; max-width: 240px; margin-left: auto; margin-right: auto; }
        .hero-portrait-frame img { display: block; width: 100%; aspect-ratio: 4 / 5; object-fit: cover; object-position: top; }
        .gallery-cell { position: relative; display: block; width: 100%; aspect-ratio: 4 / 5; overflow: hidden; }
        .gallery-cell img { position: absolute; inset: 0; width: 100%; height: 100%; max-width: none; object-fit: cover; object-position: top; }
        @media (min-width: 640px) { .hero-portrait { max-width: 280px; } }
        @media (min-width: 1024px) {
            .hero-portrait { margin-left: 0; margin-right: 0; }
            .hero-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 4rem; align-items: start; }
        }
        .hero-scroll-x {
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
        }
        @media (max-width: 767px) {
            .hero-portrait-frame::before { display: none; }
            .mobile-nav-panel {
                max-height: min(70vh, 28rem);
                overflow-y: auto;
                border-top: 1px solid color-mix(in srgb, var(--accent) 12%, #fff 88%);
                margin-top: 0.25rem;
                padding-top: 0.5rem;
            }
        }
    </style>
</head>
<body class="theme-body text-slate-800 font-sans antialiased" x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false">
    <header class="theme-header sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between gap-3 h-14 sm:h-16 min-w-0">
                <a href="{{ route('home') }}" class="site-brand font-serif text-base sm:text-lg font-bold text-[var(--accent)] truncate min-w-0">
                    {{ $profile->name }}@if($profile->credentials)<span class="font-normal">, {{ $profile->credentials }}</span>@endif
                </a>
                <button
                    type="button"
                    @click="mobileOpen = !mobileOpen"
                    class="md:hidden shrink-0 p-2.5 rounded-lg hover:bg-slate-100 text-slate-700"
                    :aria-expanded="mobileOpen.toString()"
                    aria-controls="mobile-nav"
                    aria-label="Toggle menu"
                >
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <nav class="hidden md:flex items-center gap-4 lg:gap-5 text-sm font-medium flex-wrap justify-end">
                    @foreach([
                        'home' => 'Home',
                        'about' => 'Biography',
                        'publications' => 'Publications',
                        'research' => 'Research',
                        'students' => 'Scholars',
                        'training' => 'Training',
                        'services' => 'Services',
                        'gallery' => 'Gallery',
                        'contact' => 'Contact',
                    ] as $route => $label)
                        <a href="{{ route($route) }}" class="hover:text-[var(--secondary)] whitespace-nowrap {{ request()->routeIs($route) ? 'text-[var(--accent)] border-b-2 border-[var(--accent)] pb-1' : 'text-slate-600' }}">{{ $label }}</a>
                    @endforeach
                    <a href="{{ auth()->check() ? url('/admin') : route('filament.admin.auth.login') }}"
                       class="hover:text-[var(--secondary)] text-slate-600 whitespace-nowrap">Login</a>
                </nav>
            </div>
            <nav
                id="mobile-nav"
                x-show="mobileOpen"
                x-cloak
                x-transition.opacity
                class="md:hidden mobile-nav-panel pb-3 space-y-0.5"
            >
                @foreach([
                    'home' => 'Home',
                    'about' => 'Biography',
                    'publications' => 'Publications',
                    'research' => 'Research',
                    'students' => 'Scholars',
                    'training' => 'Training',
                    'services' => 'Services',
                    'gallery' => 'Gallery',
                    'contact' => 'Contact',
                ] as $route => $label)
                    <a
                        href="{{ route($route) }}"
                        @click="mobileOpen = false"
                        class="block py-2.5 px-1 text-slate-700 hover:text-[var(--accent)] {{ request()->routeIs($route) ? 'text-[var(--accent)] font-semibold' : '' }}"
                    >{{ $label }}</a>
                @endforeach
                <a
                    href="{{ auth()->check() ? url('/admin') : route('filament.admin.auth.login') }}"
                    @click="mobileOpen = false"
                    class="block py-2.5 px-1 text-slate-700 hover:text-[var(--accent)]"
                >Login</a>
            </nav>
        </div>
    </header>

    <main class="min-w-0 overflow-x-hidden">
        @yield('content')
    </main>

    <footer class="bg-[var(--accent)] text-white mt-10 sm:mt-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 text-center text-sm text-white/70">
            &copy; {{ date('Y') }} {{ $profile->name }}. All rights reserved.
        </div>
    </footer>
</body>
</html>
