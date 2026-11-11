@php
    $menu = [
        'home' => 'Home', 'music' => 'Music', 'visual-art' => 'Visual Art', 'theatre' => 'Theatre',
        'portfolio' => 'Portfolio', 'about' => 'About Me', 'contacts' => 'Contacts',
    ];
    $social = [
        'facebook' => ['Facebook', setting('social_facebook')],
        'instagram' => ['Instagram', setting('social_instagram')],
        'spotify' => ['Spotify', setting('social_spotify')],
        'youtube' => ['YouTube', setting('social_youtube')],
        'whatsapp' => ['WhatsApp', setting('social_whatsapp')],
    ];

    // ---- SEO (set in admin › SEO) ----
    $brand = setting('seo_title', 'Jaya Barman');
    $metaDesc = trim((string) (View::getSection('meta_description') ?: setting('seo_description', 'Jaya Barman: musician, visual artist and actor. Music, visual art, theatre and portfolio.')));
    $metaKeywords = (string) setting('seo_keywords');
    $ogImage = setting('seo_image') ? media(setting('seo_image')) : asset('assets/img/hero.jpg');
    $allowIndex = (string) setting('seo_index', '1') !== '0';
    $pageTitle = trim((string) View::getSection('title'));
    $fullTitle = ($pageTitle && $pageTitle !== $brand) ? $pageTitle.' | '.$brand : $brand;
    $canonical = url()->current();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    @if ($metaKeywords)<meta name="keywords" content="{{ $metaKeywords }}">@endif
    <meta name="robots" content="{{ $allowIndex ? 'index, follow' : 'noindex, nofollow' }}">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="icon" href="{{ asset('assets/img/logo.svg') }}" type="image/svg+xml">

    {{-- Open Graph / Twitter (link previews) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Poppins:wght@300;400;500;600&family=Roboto+Slab:wght@100;300;400&family=Roboto:wght@100;300;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}" data-turbo-track="reload">

    {{-- Turbo: links and forms load in the background, only the page body is swapped (no full reload) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.12/dist/turbo.es2017-umd.js" defer></script>
    <script src="{{ asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}" defer data-turbo-track="reload"></script>
    <meta name="turbo-prefetch" content="true">
    @stack('head')
</head>
<body class="@yield('body_class')">

<header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark p-0">
        <div class="container-fluid justify-content-center">
            <a class="navbar-brand d-lg-none" href="{{ route('home') }}">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Jaya Barman" width="73" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Open menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="mainNav">
                <ul class="navbar-nav align-items-lg-center">
                    @foreach ($menu as $route => $label)
                        <li class="nav-item"><a @class(['nav-link', 'active' => request()->routeIs($route)]) href="{{ route($route) }}" @if (request()->routeIs($route)) aria-current="page" @endif>{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="main">
@yield('content')
</main>

<footer class="site-footer">
    <ul class="social-icons">
        @foreach ($social as $icon => [$label, $url])
            @if ($url)
                <li><a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}"><i class="bi bi-{{ $icon }}"></i></a></li>
            @endif
        @endforeach
    </ul>
</footer>

<!-- Lightbox for images and videos (used on every gallery) -->
<div class="modal fade lightbox" id="lightbox" tabindex="-1" aria-label="Media viewer" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <button type="button" class="lb-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <button type="button" class="lb-nav lb-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
            <button type="button" class="lb-nav lb-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
            <div class="lb-body"></div>
            <div class="lb-caption"></div>
        </div>
    </div>
</div>

</body>
</html>
