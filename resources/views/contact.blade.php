@extends('layouts.app')

@section('title', 'Contact | '.$profile->name)

@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-serif text-4xl font-bold text-[var(--accent)] mb-8">Contact</h1>
    <div class="theme-surface rounded-xl border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-8">
        @if($contactMessage)
            <p class="text-slate-600 mb-8">{{ $contactMessage }}</p>
        @endif
        <div class="space-y-4">
            @if($profile->email)
                <div>
                    <h3 class="font-semibold text-slate-800">Email</h3>
                    <a href="mailto:{{ $profile->email }}" class="text-[var(--secondary)] hover:underline">{{ $profile->email }}</a>
                </div>
            @endif
            @if($profile->phone)
                <div>
                    <h3 class="font-semibold text-slate-800">Phone</h3>
                    <a href="tel:{{ preg_replace('/\s+/', '', $profile->phone) }}" class="text-[var(--secondary)] hover:underline">{{ $profile->phone }}</a>
                </div>
            @endif
            @if($profile->whatsappUrl())
                <div>
                    <h3 class="font-semibold text-slate-800">WhatsApp</h3>
                    <a href="{{ $profile->whatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="text-[var(--secondary)] hover:underline">{{ $profile->whatsapp }}</a>
                </div>
            @endif
            @if($profile->affiliation)
                <div>
                    <h3 class="font-semibold text-slate-800">Affiliation</h3>
                    <p class="text-slate-600">{{ $profile->affiliation }}</p>
                    @if($profile->secondary_affiliation)
                        <p class="text-slate-600 mt-1">{{ $profile->secondary_affiliation }}</p>
                    @endif
                </div>
            @endif
            @if($profile->location)
                <div>
                    <h3 class="font-semibold text-slate-800">Location</h3>
                    <p class="text-slate-600">{{ $profile->location }}</p>
                </div>
            @endif
        </div>
        @if($profile->hasCv())
            <div class="mt-8 pt-6 border-t border-slate-200">
                <h3 class="font-semibold text-slate-800 mb-3">Curriculum Vitae</h3>
                <p class="text-slate-600 text-sm mb-4">
                    @if(\App\Support\CvAccess::requiresKey())
                        A download key is required to access the CV.
                    @else
                        Download the latest curriculum vitae.
                    @endif
                </p>
                @include('partials.cv-link')
            </div>
        @endif
        <div class="mt-8 pt-6 border-t border-slate-200">
            <h3 class="font-semibold text-slate-800 mb-3">Academic Profiles</h3>
            <div class="flex flex-wrap gap-3">
                @foreach($academicProfiles as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="noopener" class="text-sm bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-lg text-[var(--accent)]">{{ $link->platform_label }}</a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
