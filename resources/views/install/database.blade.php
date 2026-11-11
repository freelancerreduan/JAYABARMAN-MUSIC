@extends('install.layout')

@section('title', 'Database')

@section('content')
  <div class="steps"><span class="done"><i class="bi bi-check2"></i>1 License</span><span class="on"><i class="bi bi-database"></i>2 Database</span></div>

  <h1><span class="ic"><i class="bi bi-database"></i></span>Connect the database</h1>
  <p class="lead">Enter the MySQL details from your hosting panel (cPanel › MySQL Databases). If the database already has the website's tables, they are kept as they are; if it is empty, the tables are created for you.</p>

  @if ($error)<div class="error"><i class="bi bi-exclamation-triangle"></i><span>{{ $error }}</span></div>@endif

  <form method="POST" action="" data-busy="Setting up…" autocomplete="off">
    <input type="hidden" name="__inst" value="1">
    <input type="hidden" name="_t" value="{{ $token }}">
    <div class="row">
      <div class="field"><label for="host">Host</label><input id="host" name="host" value="{{ $db['host'] ?: 'localhost' }}" required></div>
      <div class="field"><label for="port">Port</label><input id="port" name="port" value="{{ $db['port'] ?: '3306' }}" inputmode="numeric" required></div>
    </div>
    <div class="field"><label for="database">Database name</label><input id="database" name="database" value="{{ $db['database'] ?? '' }}" placeholder="e.g. cpaneluser_site" required></div>
    <div class="field"><label for="username">Database user</label><input id="username" name="username" value="{{ $db['username'] ?? '' }}" required></div>
    <div class="field"><label for="password">Database password</label><input id="password" name="password" type="password" autocomplete="new-password"></div>

    <div class="sep" style="margin-top:8px">
      <label style="color:var(--teal)">Admin login <span style="text-transform:none;letter-spacing:0;color:var(--muted)">(used to create the account when the database is empty)</span></label>
      <div class="field" style="margin-top:10px"><label for="admin_email">Admin email</label><input id="admin_email" name="admin_email" type="email" value=""></div>
      <div class="field"><label for="admin_password">Admin password</label><input id="admin_password" name="admin_password" type="password" autocomplete="new-password" minlength="8"></div>
    </div>

    <button class="btn" type="submit"><i class="bi bi-plug"></i>Connect &amp; finish</button>
  </form>
  <div class="foot">The details are saved in the website's .env file.</div>
@endsection
