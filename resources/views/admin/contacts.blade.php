@extends('layouts.admin')

@section('title', 'Contacts')
@section('subtitle', 'Photo and contact details')
@section('view_url', route('contacts'))

@section('content')
  <form method="POST" action="{{ route('admin.contacts') }}" enctype="multipart/form-data" data-busy="Saving…">
    @csrf
    @method('PUT')

    <div class="grid g-form">
      <div class="card">
        <div class="card-head"><div><h3>Contact details</h3><p>Leave a box empty to hide that line.</p></div></div>
        <div class="card-body form-grid">
          <div class="field full">
            <label for="contact_title">Heading</label>
            <input id="contact_title" name="contact_title" class="input" value="{{ old('contact_title', setting('contact_title', 'Get in Touch')) }}" required>
          </div>
          <div class="field">
            <label for="contact_location">Location</label>
            <input id="contact_location" name="contact_location" class="input" value="{{ old('contact_location', setting('contact_location')) }}" placeholder="Paris, France">
          </div>
          <div class="field">
            <label for="contact_email">E-mail</label>
            <input id="contact_email" name="contact_email" type="email" @class(['input', 'is-invalid' => $errors->has('contact_email')]) value="{{ old('contact_email', setting('contact_email')) }}">
            @error('contact_email')<div class="error">{{ $message }}</div>@enderror
          </div>
          <div class="field full">
            <label for="contact_whatsapp">WhatsApp number or link</label>
            <input id="contact_whatsapp" name="contact_whatsapp" class="input" value="{{ old('contact_whatsapp', setting('contact_whatsapp')) }}" placeholder="+8801711000000">
            <div class="hint">The number is not shown on the page. Visitors see a “Message on WhatsApp” link.</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><div><h3>Photo</h3><p>Shown at the top of the page.</p></div></div>
        <div class="card-body">
          <img src="{{ media(setting('contact_image', 'assets/img/jaya-live.jpg')) }}" alt="" class="w-100 rounded-3 mb-3" style="aspect-ratio:4/3;object-fit:cover">
          @include('admin.partials.dropzone', ['name' => 'contact_image_file', 'title' => 'Change photo', 'hint' => 'JPG or PNG', 'small' => true])
        </div>
      </div>
    </div>

    <div class="mt d-flex justify-content-end">
      <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>Save Contacts page</button>
    </div>
  </form>
@endsection
