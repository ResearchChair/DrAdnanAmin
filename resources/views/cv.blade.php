@extends('layouts.app')

@section('title', 'Download CV | '.$profile->name)

@section('content')
<section class="max-w-lg mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-serif text-3xl font-bold text-[var(--accent)] mb-3">Download CV</h1>
    <p class="text-slate-600 text-sm mb-8">
        Enter the access key provided by {{ $profile->name }} to download the curriculum vitae.
    </p>

    <div class="theme-surface border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-8">
        <form method="POST" action="{{ route('cv.download') }}" class="space-y-5">
            @csrf
            <div>
                <label for="key" class="block text-sm font-semibold text-slate-800 mb-2">Access key</label>
                <input
                    type="password"
                    name="key"
                    id="key"
                    required
                    autofocus
                    class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-800 focus:border-[var(--accent)] focus:ring-2 focus:ring-[var(--accent)]/20 outline-none"
                    placeholder="Enter download key"
                >
                @error('key')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if($error ?? false)
                    <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
                @endif
            </div>
            <button
                type="submit"
                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-[var(--accent)] text-white font-semibold text-sm hover:opacity-90 transition-opacity"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download {{ $profile->cv_label ?? 'CV' }}
            </button>
        </form>
    </div>
</section>
@endsection
