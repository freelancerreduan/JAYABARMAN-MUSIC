<!doctype html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in · Jaya Barman Admin</title>
  {{-- Login and logout always load the full page --}}
  <meta name="turbo-visit-control" content="reload">
  <script>try { if (localStorage.getItem("admin-theme") === "dark") document.documentElement.dataset.theme = "dark"; } catch (e) {}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('admin-assets/admin.css') }}?v={{ filemtime(public_path('admin-assets/admin.css')) }}" rel="stylesheet">
  <link href="{{ asset('admin-assets/admin-extra.css') }}?v={{ filemtime(public_path('admin-assets/admin-extra.css')) }}" rel="stylesheet">
</head>
<body>
  <div class="auth">
    <div class="auth-art" style="--auth-img:url('{{ media(setting('about_photo', 'assets/img/portrait.jpg')) }}')">
      <div class="brand-mark">JAYA BARMAN</div>
      <div class="brand-sub" style="margin:8px 0 18px">Studio admin</div>
      <p>Change the website text, songs, videos and photos, all in one place.</p>
    </div>

    <div class="auth-form">
      <form class="auth-card" method="POST" action="{{ route('login') }}" data-turbo="false">
        @csrf
        <h1>Welcome back</h1>
        <p>Sign in to the admin panel.</p>

        <div class="form-grid" style="grid-template-columns:1fr">
          <div class="field">
            <label for="f_email">Email</label>
            <input id="f_email" name="email" type="email" value="{{ old('email') }}" @class(['input', 'is-invalid' => $errors->has('email')]) autocomplete="username" required autofocus>
            @error('email')<div class="error">{{ $message }}</div>@enderror
          </div>
          <div class="field">
            <label for="f_password">Password</label>
            <input id="f_password" name="password" type="password" class="input" autocomplete="current-password" required>
          </div>
          <label class="switch"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
          <button class="btn btn-primary" type="submit" style="height:44px"><i class="bi bi-box-arrow-in-right"></i>Sign in</button>
        </div>

        <p style="margin-top:22px;font-size:13px"><a href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Back to website</a></p>
      </form>
    </div>
  </div>
</body>
</html>
