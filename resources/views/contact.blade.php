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

    @if(!empty($visitorStats))
        <div class="mt-10 theme-surface rounded-xl border border-[color-mix(in_srgb,var(--accent)_12%,#fff_88%)] p-8">
            <h2 class="font-serif text-2xl font-bold text-[var(--accent)] mb-2">Website visitors</h2>
            <p class="text-sm text-slate-500 mb-6">Live portfolio traffic — unique visitors, returning visitors, pages, and countries.</p>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-2xl font-bold text-[var(--accent)] tabular-nums">{{ number_format($visitorStats['total_views']) }}</div>
                    <div class="mt-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Page views</div>
                </div>
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-2xl font-bold text-[var(--accent)] tabular-nums">{{ number_format($visitorStats['unique_visitors']) }}</div>
                    <div class="mt-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Unique visitors</div>
                </div>
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-2xl font-bold text-[var(--accent)] tabular-nums">{{ number_format($visitorStats['new_visitors']) }}</div>
                    <div class="mt-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-slate-500">First-time</div>
                </div>
                <div class="theme-surface-muted border border-[color-mix(in_srgb,var(--accent)_10%,#fff_90%)] p-4 text-center">
                    <div class="font-serif text-2xl font-bold text-[var(--accent)] tabular-nums">{{ number_format($visitorStats['repeat_visitors']) }}</div>
                    <div class="mt-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Repeat visitors</div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-4">By page</h3>
                    @forelse($visitorStats['by_page'] as $row)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 last:border-0 text-sm">
                            <div class="min-w-0">
                                <div class="font-medium text-slate-700 truncate">{{ $row['label'] }}</div>
                                <div class="text-xs text-slate-400">{{ $row['path'] }}</div>
                            </div>
                            <div class="text-right shrink-0 tabular-nums text-slate-600">
                                <div>{{ number_format($row['views']) }} views</div>
                                <div class="text-xs text-slate-400">{{ number_format($row['unique']) }} unique</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No page views recorded yet. Stats appear as people browse the site.</p>
                    @endforelse
                </div>

                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-4">By country</h3>
                    @forelse($visitorStats['by_country'] as $row)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 last:border-0 text-sm">
                            <div class="font-medium text-slate-700">
                                {{ $row['country'] }}
                                @if($row['code'])
                                    <span class="text-xs text-slate-400 font-normal">({{ $row['code'] }})</span>
                                @endif
                            </div>
                            <div class="text-right shrink-0 tabular-nums text-slate-600">
                                <div>{{ number_format($row['views']) }} views</div>
                                <div class="text-xs text-slate-400">{{ number_format($row['unique']) }} unique</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Country data appears once visitors are tracked.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
