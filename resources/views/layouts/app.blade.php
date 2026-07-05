<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $profile->name.' | Academic Portfolio')</title>
    <meta name="description" content="@yield('meta_description', $metaDescription ?? 'Academic portfolio')">
    <meta property="og:title" content="@yield('title', $profile->name)">
    <meta property="og:description" content="{{ $metaDescription ?? '' }}">
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
    </style>
</head>
<body class="theme-body text-slate-800 font-sans antialiased" x-data="{ mobileOpen: false }">
    <header class="theme-header sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="font-serif text-lg font-bold text-[var(--accent)]">
                    {{ $profile->name }}@if($profile->credentials), {{ $profile->credentials }}@endif
                </a>
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-slate-100" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                    @foreach([
                        'home' => 'Home',
                        'about' => 'Biography',
                        'publications' => 'Publications',
                        'research' => 'Research',
                        'students' => 'Students',
                        'training' => 'Training',
                        'gallery' => 'Gallery',
                        'contact' => 'Contact',
                    ] as $route => $label)
                        <a href="{{ route($route) }}" class="hover:text-[var(--secondary)] {{ request()->routeIs($route) ? 'text-[var(--accent)] border-b-2 border-[var(--accent)] pb-1' : 'text-slate-600' }}">{{ $label }}</a>
                    @endforeach
                    <a href="{{ auth()->check() ? url('/admin') : route('filament.admin.auth.login') }}"
                       class="hover:text-[var(--secondary)] text-slate-600">Login</a>
                </nav>
            </div>
            <nav x-show="mobileOpen" x-cloak class="md:hidden pb-4 space-y-2">
                @foreach(['home','about','publications','research','students','training','gallery','contact'] as $route)
                    <a href="{{ route($route) }}" class="block py-2 text-slate-700 hover:text-[var(--accent)]">{{ ucfirst($route === 'about' ? 'Biography' : $route) }}</a>
                @endforeach
                <a href="{{ auth()->check() ? url('/admin') : route('filament.admin.auth.login') }}"
                   class="block py-2 text-slate-700 hover:text-[var(--accent)]">Login</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-[var(--accent)] text-white mt-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 text-center text-sm text-white/70">
            &copy; {{ date('Y') }} {{ $profile->name }}. All rights reserved.
        </div>
    </footer>
</body>
</html>
