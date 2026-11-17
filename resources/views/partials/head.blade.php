<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php($applicationName = __('Osun Marketing Directory'))

<title>
    {{ filled($title ?? null) && $title !== $applicationName ? $title.' - '.$applicationName : $applicationName }}
</title>

@if (filled($ogTitle ?? null) && $ogTitle !== $applicationName)
    <meta property="og:title" content="{{ $ogTitle.' - '.$applicationName }}">
@else
    <meta property="og:title" content="{{ $applicationName }}">
@endif
<meta property="og:description" content="{{ $ogDescription ?? __('Discover approved local businesses and services across Osun State.') }}">
<meta property="og:image" content="{{ $ogImage ?? asset('favicon.svg') }}">
<meta property="og:url" content="{{ $ogUrl ?? url('/directory') }}">

@if ($ogJsonLd ?? false)
    <script type="application/ld+json">{{ $ogJsonLd }}</script>
@endif

<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
