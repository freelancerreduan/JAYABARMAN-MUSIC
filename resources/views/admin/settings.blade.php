@extends('layouts.admin')

@section('title', 'Footer links & login')
@section('subtitle', 'Social icons at the bottom of every page, and your login')

@section('content')
  {{-- ---------------- SEO ---------------- --}}
  <form method="POST" action="{{ route('admin.seo') }}" enctype="multipart/form-data" class="card mb-4">
    @csrf
    @method('PUT')
    <div class="card-head"><div><h3><i class="bi bi-search text-primary"></i> SEO</h3><p>How the website shows up on Google and when a link is shared. Leave a box empty to use the default.</p></div></div>
    <div class="card-body form-grid" style="grid-template-columns:1fr">
      <div class="field">
        <label for="seo_title">Site title</label>
        <input id="seo_title" name="seo_title" class="input" value="{{ old('seo_title', setting('seo_title', 'Jaya Barman')) }}" maxlength="70" placeholder="Jaya Barman">
        <div class="hint">Shown on the browser tab and as the blue Google headline. Keep it short.</div>
      </div>
      <div class="field">
        <label for="seo_description">Meta description</label>
        <textarea id="seo_description" name="seo_description" class="input" rows="3" maxlength="200" placeholder="One or two lines about Jaya Barman.">{{ old('seo_description', setting('seo_description')) }}</textarea>
        <div class="hint">The grey text under the title on Google (up to ~160 letters).</div>
      </div>
      <div class="field">
        <label for="seo_keywords">Keywords <span class="form-label-sm">(optional)</span></label>
        <input id="seo_keywords" name="seo_keywords" class="input" value="{{ old('seo_keywords', setting('seo_keywords')) }}" maxlength="255" placeholder="jaya barman, bengali music, visual art, theatre">
      </div>
      <div class="field">
        <label>Share image <span class="form-label-sm">(shown when the link is shared on Facebook/WhatsApp)</span></label>
        @if (setting('seo_image'))
          <img src="{{ media(setting('seo_image')) }}" alt="" style="max-width:220px;border-radius:10px;border:1px solid var(--line);margin-bottom:8px;display:block">
        @endif
        @include('admin.partials.dropzone', ['name' => 'seo_image_file', 'accept' => 'image/*', 'title' => 'Drag an image or click', 'hint' => 'Wide image works best (1200×630). Leave empty to keep the current one.', 'small' => true])
      </div>
      <div class="field">
        <label class="d-flex align-items-center gap-2" style="cursor:pointer">
          <input type="hidden" name="seo_index" value="0">
          <input type="checkbox" name="seo_index" value="1" @checked((string) setting('seo_index', '1') !== '0')>
          <span>Let search engines (Google) show this website</span>
        </label>
        <div class="hint">Turn off only while the site is not ready. When off, <code>robots.txt</code> and the pages ask search engines to stay away.</div>
      </div>
    </div>
    <div class="card-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>Save SEO</button></div>
  </form>

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
