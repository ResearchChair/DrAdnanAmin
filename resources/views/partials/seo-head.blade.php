@php
    $seo = $seo ?? null;
    $pageTitle = $seo['title'] ?? trim($__env->yieldContent('title', $profile->name.' | Academic Portfolio'));
    $pageDescription = $seo['description'] ?? trim($__env->yieldContent('meta_description', $metaDescription ?? 'Academic portfolio'));
    $canonical = $seo['canonical'] ?? url()->current();
    $ogImage = $seo['image'] ?? null;
    $ogType = $seo['type'] ?? 'website';
    $robots = $seo['robots'] ?? 'index,follow';
    $siteName = $seo['site_name'] ?? ($profile->name.' Academic Portfolio');
    $locale = $seo['locale'] ?? str_replace('_', '-', app()->getLocale());
    $keywords = $seo['keywords'] ?? null;
    $twitter = $seo['twitter_handle'] ?? null;
@endphp
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
@if($keywords)
    <meta name="keywords" content="{{ $keywords }}">
@endif
<meta name="robots" content="{{ $robots }}">
<meta name="author" content="{{ $profile->name }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ $locale }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $profile->name }}">
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
@if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif
@if($twitter)
    <meta name="twitter:site" content="@{{ $twitter }}">
    <meta name="twitter:creator" content="@{{ $twitter }}">
@endif

@if(!empty($seo['json_ld']))
    @foreach($seo['json_ld'] as $graph)
        <script type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endif
