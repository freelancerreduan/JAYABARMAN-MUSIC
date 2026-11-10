@extends('layouts.admin')

@section('title', 'Footer links & login')
@section('subtitle', 'Social icons at the bottom of every page, and your login')

@section('content')
  <div class="grid g-2">
    <form method="POST" action="{{ route('admin.settings') }}" class="card">
      @csrf
      @method('PUT')
      <div class="card-head"><div><h3>Footer social links</h3><p>Leave a box empty to hide that icon.</p></div></div>
      <div class="card-body form-grid" style="grid-template-columns:1fr">
        @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'spotify' => 'Spotify', 'youtube' => 'YouTube', 'whatsapp' => 'WhatsApp'] as $key => $label)
          <div class="field">
            <label for="s_{{ $key }}"><i class="bi bi-{{ $key }}"></i> {{ $label }} link</label>
            <input id="s_{{ $key }}" name="social_{{ $key }}" type="url" @class(['input', 'is-invalid' => $errors->has('social_'.$key)]) value="{{ old('social_'.$key, setting('social_'.$key)) }}" placeholder="https://">
            @error('social_'.$key)<div class="error">{{ $message }}</div>@enderror
          </div>
        @endforeach
      </div>
      <div class="card-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>Save links</button></div>
    </form>

    <form method="POST" action="{{ route('admin.account') }}" class="card">
      @csrf
      @method('PUT')
      <div class="card-head"><div><h3>Login</h3><p>To change the password, fill the last three boxes.</p></div></div>
      <div class="card-body form-grid" style="grid-template-columns:1fr">
        <div class="field">
          <label for="a_name">Your name</label>
          <input id="a_name" name="name" class="input" value="{{ old('name', auth()->user()->name) }}" required>
        </div>
        <div class="field">
          <label for="a_email">Login email</label>
          <input id="a_email" name="email" type="email" @class(['input', 'is-invalid' => $errors->has('email')]) value="{{ old('email', auth()->user()->email) }}" required>
          @error('email')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
          <label for="a_current">Current password</label>
          <input id="a_current" name="current_password" type="password" @class(['input', 'is-invalid' => $errors->has('current_password')]) autocomplete="current-password">
          @error('current_password')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
          <label for="a_new">New password</label>
          <input id="a_new" name="password" type="password" @class(['input', 'is-invalid' => $errors->has('password')]) autocomplete="new-password">
          @error('password')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
          <label for="a_new2">New password again</label>
          <input id="a_new2" name="password_confirmation" type="password" class="input" autocomplete="new-password">
        </div>
      </div>
      <div class="card-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>Save login</button></div>
    </form>
  </div>
@endsection
