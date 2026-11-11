@extends('install.layout')

@section('title', 'License')

@php
  $status = $license['s'] ?? null;            // expired / suspended when a saved live key stopped working
  $locked = in_array($status, ['expired', 'suspended'], true);
  $price = isset($license['price']) ? number_format((float) $license['price'], 0) : null;
@endphp

@section('content')
  <div class="steps"><span class="on"><i class="bi bi-key"></i>1 License</span><span><i class="bi bi-database"></i>2 Database</span></div>

  @if ($local)
    {{-- Developer machine: unlock with the developer name, no key, no server call --}}
    <h1><span class="ic"><i class="bi bi-code-slash"></i></span>Developer access</h1>
    <p class="lead">This is a local machine (<span class="domain">{{ $domain }}</span>). Enter the developer name to open the site for testing. On a live website address the real license key is required instead.</p>

    @if ($error)<div class="error"><i class="bi bi-exclamation-triangle"></i><span>{{ $error }}</span></div>@endif

    <form method="POST" action="" data-busy="Opening…">
      <input type="hidden" name="__lic" value="1">
      <input type="hidden" name="_t" value="{{ $token }}">
      <div class="field">
        <label for="dev">Developer name</label>
        <input id="dev" name="dev" value="" placeholder="Enter developer name" autocomplete="off" required autofocus>
        <div class="hint">Local testing only. This never opens a live website address.</div>
      </div>
      <button class="btn" type="submit"><i class="bi bi-unlock"></i>Open for development</button>
    </form>
  @else
    {{-- Live domain: real license key --}}
    @if ($locked)
      <h1><span class="ic"><i class="bi bi-lock"></i></span>License {{ $status === 'expired' ? 'expired' : 'suspended' }}</h1>
      <p class="lead">
        @if ($status === 'expired')
          The license for <span class="domain">{{ $domain }}</span> ended on <b>{{ \Illuminate\Support\Carbon::parse($license['to'])->format('j F Y') }}</b>. Renew it to open the website again.
        @else
          The license for <span class="domain">{{ $domain }}</span> has been suspended. Please contact us to activate it again.
        @endif
      </p>
      <div class="box">
        <h3>License holder</h3>
        <ul class="info">
          @if (! empty($license['name']))<li><i class="bi bi-person"></i><span><b>{{ $license['name'] }}</b></span></li>@endif
          @if (! empty($license['email']))<li><i class="bi bi-envelope"></i><span>{{ $license['email'] }}</span></li>@endif
          @if (! empty($license['phone']))<li><i class="bi bi-telephone"></i><span>{{ $license['phone'] }}</span></li>@endif
          @if ($price)<li><i class="bi bi-cash-coin"></i><span>Renewal fee: <span class="price">৳{{ $price }}</span></span></li>@endif
        </ul>
      </div>
    @else
      <h1><span class="ic"><i class="bi bi-shield-lock"></i></span>Activate your license</h1>
      <p class="lead">This website needs a license key for <span class="domain">{{ $domain }}</span> before it can be used.</p>
    @endif

    @if ($error)<div class="error"><i class="bi bi-exclamation-triangle"></i><span>{{ $error }}</span></div>@endif

    <form method="POST" action="" data-busy="Checking the key…">
      <input type="hidden" name="__lic" value="1">
      <input type="hidden" name="_t" value="{{ $token }}">
      <div class="field">
        <label for="key">License key</label>
        <input class="key" id="key" name="key" value="{{ $locked ? $key : '' }}" placeholder="XXXX-XXXX-XXXX-XXXX" autocomplete="off" required autofocus>
        <div class="hint">{{ $locked ? 'After renewing, press the button to check the key again.' : 'You get the key when you buy the license.' }}</div>
      </div>
      <button class="btn" type="submit"><i class="bi bi-patch-check"></i>{{ $locked ? 'Check again' : 'Activate' }}</button>
    </form>

    <div class="sep">
      <div class="box" style="margin:0">
        <h3>{{ $locked ? 'Renew or get help' : 'Buy a license' }}</h3>
        <ul class="info">
          <li><i class="bi bi-building"></i><span><b>{{ $vendor['name'] }}</b></span></li>
          @if ($vendor['phone'])<li><i class="bi bi-telephone"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', $vendor['phone']) }}" style="color:var(--text)">{{ $vendor['phone'] }}</a></li>@endif
          @if ($vendor['email'])<li><i class="bi bi-envelope"></i><a href="mailto:{{ $vendor['email'] }}" style="color:var(--text)">{{ $vendor['email'] }}</a></li>@endif
          @if ($vendor['website'])<li><i class="bi bi-globe2"></i><a href="{{ $vendor['website'] }}" target="_blank" rel="noopener" style="color:var(--text)">{{ preg_replace('#^https?://#', '', $vendor['website']) }}</a></li>@endif
        </ul>
      </div>
    </div>
  @endif
@endsection
