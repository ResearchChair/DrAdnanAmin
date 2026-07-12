@extends('layouts.app')

@section('title', 'Services | '.$profile->name)

@section('content')
<style>
    .svc-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        padding: 0.375rem;
        margin-bottom: 1.5rem;
        border-radius: 0.875rem;
        background: color-mix(in srgb, var(--accent) 5%, var(--surface-muted, #f5ebe8) 95%);
        border: 1px solid color-mix(in srgb, var(--accent) 12%, #fff 88%);
    }
    .svc-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5625rem 0.875rem;
        border-radius: 0.625rem;
        border: 1px solid transparent;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
        background: transparent;
        cursor: pointer;
    }
    .svc-tab--active {
        color: var(--accent);
        background: var(--surface, #fff9f5);
        border-color: color-mix(in srgb, var(--accent) 18%, #fff 82%);
        box-shadow: 0 1px 2px color-mix(in srgb, var(--accent) 10%, transparent 90%);
    }
    .svc-tab__count {
        min-width: 1.5rem;
        height: 1.5rem;
        padding: 0 0.4375rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--accent) 8%, #fff 92%);
        color: #64748b;
    }
    .svc-tab--active .svc-tab__count { color: #fff; background: var(--accent); }
    .svc-card {
        background: var(--surface, #fff);
        border: 1px solid color-mix(in srgb, var(--accent) 12%, #e2e8f0 88%);
        border-radius: 0.75rem;
        padding: 1.25rem;
    }
    .svc-badge {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--accent);
        background: color-mix(in srgb, var(--accent) 10%, #fff 90%);
        padding: 0.2rem 0.5rem;
        border-radius: 0.25rem;
    }
</style>

<section
    class="max-w-6xl mx-auto px-4 sm:px-6 py-12"
    x-data="{ tab: @js($defaultServicesTab) }"
>
    <div class="mb-6">
        <h1 class="font-serif text-3xl font-bold text-[var(--accent)] mb-2">Services</h1>
        <p class="text-sm text-slate-600 max-w-3xl">
            Consultancy engagements and software solutions delivered for organizations over time.
        </p>
    </div>

    <div class="svc-tabs" role="tablist" aria-label="Service categories">
        <button type="button" role="tab" class="svc-tab" :class="{ 'svc-tab--active': tab === 'consultancy' }" @click="tab = 'consultancy'">
            <span>Consultancy</span>
            <span class="svc-tab__count">{{ $consultancyEngagements->count() }}</span>
        </button>
        <button type="button" role="tab" class="svc-tab" :class="{ 'svc-tab--active': tab === 'software' }" @click="tab = 'software'">
            <span>Software Solutions</span>
            <span class="svc-tab__count">{{ $softwareSolutions->count() }}</span>
        </button>
    </div>

    <div x-show="tab === 'consultancy'" x-cloak class="space-y-4">
        <p class="text-sm text-slate-500 mb-2">Advisory and consultancy provided to academic, public, and private organizations.</p>
        @forelse($consultancyEngagements as $item)
            <article class="svc-card">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="svc-badge">{{ $item->type_label }}</span>
                    @if($item->role)
                        <span class="text-xs text-slate-500">{{ $item->role }}</span>
                    @endif
                    @if($item->yearRangeLabel())
                        <span class="text-xs text-slate-400 tabular-nums">{{ $item->yearRangeLabel() }}</span>
                    @endif
                </div>
                <h2 class="font-semibold text-lg text-slate-800">{{ $item->title }}</h2>
                <p class="text-slate-600 mt-1">{{ $item->organization }}@if($item->location) · {{ $item->location }}@endif</p>
                @if($item->description)
                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">{{ $item->description }}</p>
                @endif
                @if($item->url)
                    <a href="{{ $item->url }}" target="_blank" rel="noopener" class="inline-block mt-3 text-sm text-[var(--secondary)] hover:underline">Learn more</a>
                @endif
            </article>
        @empty
            <p class="text-sm text-slate-500">No consultancy engagements listed yet.</p>
        @endforelse
    </div>

    <div x-show="tab === 'software'" x-cloak class="space-y-4">
        <p class="text-sm text-slate-500 mb-2">Custom software and digital solutions developed for partner organizations.</p>
        @forelse($softwareSolutions as $item)
            <article class="svc-card">
                <div class="flex gap-4">
                    @if($item->logoUrl())
                        <img src="{{ $item->logoUrl() }}" alt="" class="w-14 h-14 object-contain rounded-lg border border-slate-100 bg-white shrink-0">
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="svc-badge">{{ $item->type_label }}</span>
                            @if($item->year)
                                <span class="text-xs text-slate-400 tabular-nums">{{ $item->year }}</span>
                            @endif
                        </div>
                        <h2 class="font-semibold text-lg text-slate-800">{{ $item->name }}</h2>
                        @if($item->tagline)
                            <p class="text-sm text-slate-500 mt-0.5">{{ $item->tagline }}</p>
                        @endif
                        <p class="text-slate-600 mt-1">{{ $item->organization }}</p>
                        @if($item->description)
                            <p class="text-sm text-slate-500 mt-2 leading-relaxed">{{ $item->description }}</p>
                        @endif
                        @if(count($item->techStackList()) > 0)
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach($item->techStackList() as $tech)
                                    <span class="text-[0.65rem] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded">{{ $tech }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if($item->url)
                            <a href="{{ $item->url }}" target="_blank" rel="noopener" class="inline-block mt-3 text-sm text-[var(--secondary)] hover:underline">View solution</a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <p class="text-sm text-slate-500">No software solutions listed yet.</p>
        @endforelse
    </div>
</section>
@endsection
