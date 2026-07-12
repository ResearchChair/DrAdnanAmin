@if($socialLinks->isNotEmpty() || $profile->whatsappUrl())
@php
    $isHero = ($variant ?? '') === 'hero';
    $isSidebar = ($placement ?? '') === 'sidebar';
@endphp
<div @class([
    $isHero && $isSidebar ? 'mt-6 pt-5 border-t border-white/15' : '',
    $isHero && ! $isSidebar ? '' : '',
    ! $isHero ? 'mt-8' : '',
])>
    <h3 @class([
        'text-xs font-semibold uppercase tracking-[0.2em] mb-3',
        'text-white/70 text-center sm:text-left' => $isHero,
        'text-slate-500' => ! $isHero,
    ])>Connect</h3>
    <div @class([
        'flex flex-wrap gap-2.5',
        'justify-center sm:justify-start' => $isHero,
        'justify-center' => ! $isHero,
    ])>
        @if($profile->whatsappUrl())
            <a href="{{ $profile->whatsappUrl() }}"
               target="_blank"
               rel="noopener noreferrer"
               @class([
                   $isHero ? 'hero-social-btn' : 'inline-flex items-center justify-center w-10 h-10 text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-[var(--accent)] border border-slate-200 transition-colors',
               ])
               title="WhatsApp"
               aria-label="WhatsApp">
                @include('partials.platform-icon', ['platform' => 'whatsapp', 'class' => 'w-[1.125rem] h-[1.125rem]'])
            </a>
        @endif
        @foreach($socialLinks as $link)
            <a href="{{ $link->url }}"
               target="_blank"
               rel="noopener noreferrer"
               @class([
                   $isHero ? 'hero-social-btn' : 'inline-flex items-center justify-center w-10 h-10 text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-[var(--accent)] border border-slate-200 transition-colors',
               ])
               title="{{ $link->label ?: $link->platform_label }}"
               aria-label="{{ $link->label ?: $link->platform_label }}">
                @include('partials.platform-icon', ['platform' => $link->platform, 'class' => 'w-[1.125rem] h-[1.125rem]'])
            </a>
        @endforeach
    </div>
</div>
@endif
