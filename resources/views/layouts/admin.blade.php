@php
    $user = auth()->user();
    $pages = [
        ['admin.home', 'house-door', 'Home', 'home'],
        ['admin.music', 'music-note-beamed', 'Music', 'music'],
        ['admin.gallery', 'palette', 'Visual Art', 'visual-art', ['section' => 'visual-art']],
        ['admin.gallery', 'mask', 'Theatre', 'theatre', ['section' => 'theatre']],
        ['admin.portfolio', 'grid-3x3-gap', 'Portfolio', 'portfolio'],
        ['admin.about', 'person-vcard', 'About Me', 'about'],
        ['admin.contacts', 'envelope-paper', 'Contacts', 'contacts'],
    ];
    $isActive = function ($p) {
        if (! request()->routeIs($p[0], $p[0].'.*', $p[0] === 'admin.music' ? 'admin.videos.*' : '__none', $p[0] === 'admin.about' ? 'admin.about.*' : '__none')) {
            return false;
        }
        return ! isset($p[4]) || request()->route('section') === $p[4]['section'];
    };
@endphp
<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="sort-url" content="{{ route('admin.sort') }}">
  <title>@yield('title', 'Dashboard') · Jaya Barman Admin</title>
  <script>try { if (localStorage.getItem("admin-theme") === "dark") document.documentElement.dataset.theme = "dark"; } catch (e) {}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Bebas+Neue&family=Tiro+Bangla&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('admin-assets/admin.css') }}?v={{ filemtime(public_path('admin-assets/admin.css')) }}" rel="stylesheet">
  <link href="{{ asset('admin-assets/admin-extra.css') }}?v={{ filemtime(public_path('admin-assets/admin-extra.css')) }}" rel="stylesheet">
  @stack('head')
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="brand">
      <div class="brand-mark">JAYA BARMAN</div>
      <div class="brand-sub">Studio admin</div>
    </a>

    <nav class="side-nav">
      <div class="side-label">Main</div>
      <a href="{{ route('admin.dashboard') }}" @class(['side-link', 'active' => request()->routeIs('admin.dashboard')])><i class="bi bi-grid-1x2"></i>Dashboard</a>
      <a href="{{ route('home') }}" target="_blank" class="side-link"><i class="bi bi-globe2"></i>View website<i class="bi bi-box-arrow-up-right chev"></i></a>

      <div class="side-label">Website pages <span class="side-hint">same as the site menu</span></div>
      @foreach ($pages as $p)
        <a href="{{ route($p[0], $p[4] ?? []) }}" @class(['side-link', 'active' => $isActive($p)])><i class="bi bi-{{ $p[1] }}"></i>{{ $p[2] }}</a>
      @endforeach

      <div class="side-label">Account</div>
      <a href="{{ route('admin.settings') }}" @class(['side-link', 'active' => request()->routeIs('admin.settings')])><i class="bi bi-gear"></i>Footer links &amp; login</a>
    </nav>

    <div class="side-user">
      <span class="avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
      <div class="truncate">
        <strong class="truncate">{{ $user->name }}</strong>
        <small>Admin</small>
      </div>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" title="Log out" aria-label="Log out"><i class="bi bi-box-arrow-right"></i></button>
      </form>
    </div>
  </aside>
  <div class="scrim" data-sidebar-toggle></div>

  <div class="main">
    <header class="topbar">
      <button class="icon-btn menu-btn" type="button" data-sidebar-toggle aria-label="Menu"><i class="bi bi-list"></i></button>
      <div class="topbar-title">
        <h1>@yield('title', 'Dashboard')</h1>
        <p>@yield('subtitle', 'Website overview')</p>
      </div>

      <div class="topbar-actions">
        @hasSection('view_url')
          <a href="@yield('view_url')" target="_blank" class="btn btn-sm view-page-btn"><i class="bi bi-box-arrow-up-right"></i><span>View page</span></a>
        @endif
        <button class="icon-btn" type="button" data-theme-toggle aria-label="Toggle dark mode"><i class="bi bi-moon theme-dark"></i><i class="bi bi-sun theme-light"></i></button>

        <div class="dropdown">
          <button class="user-chip" type="button" data-dropdown>
            <span class="avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <span class="who"><strong>{{ $user->name }}</strong><small>Admin</small></span>
            <i class="bi bi-chevron-down"></i>
          </button>
          <div class="dropdown-menu">
            <a href="{{ route('admin.settings') }}"><i class="bi bi-key"></i>Login &amp; password</a>
            <a href="{{ route('home') }}" target="_blank"><i class="bi bi-globe2"></i>View website</a>
            <div class="sep"></div>
            <form method="POST" action="{{ route('admin.logout') }}">
              @csrf
              <button type="submit"><i class="bi bi-box-arrow-right"></i>Log out</button>
            </form>
          </div>
        </div>
      </div>
    </header>

    <main class="content">
      @if (session('success'))
        <div class="flash" data-autohide><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="flash error"><i class="bi bi-exclamation-triangle-fill"></i>{{ $errors->first() }}</div>
      @endif
      @yield('content')
    </main>

    <footer class="app-footer">&copy; {{ date('Y') }} Jaya Barman. All rights reserved.</footer>
  </div>
</div>

<div class="save-toast" id="saveToast" role="status" aria-live="polite"><i class="bi bi-check-circle-fill"></i><span>Order saved</span></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
@stack('scripts')
<script src="{{ asset('admin-assets/admin.js') }}?v={{ filemtime(public_path('admin-assets/admin.js')) }}"></script>
<script src="{{ asset('admin-assets/admin-extra.js') }}?v={{ filemtime(public_path('admin-assets/admin-extra.js')) }}"></script>
</body>
</html>
