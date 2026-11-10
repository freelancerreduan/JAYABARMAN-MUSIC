@extends('layouts.admin')

@section('title', 'Home')
@section('subtitle', 'Top photo and the text under it')
@section('view_url', route('home'))


@section('content')
  <form method="POST" action="{{ route('admin.home') }}" enctype="multipart/form-data" data-busy="Saving…">
    @csrf
    @method('PUT')

    <div class="grid g-form">
      <div class="card">
        <div class="card-head"><div><h3>Text</h3><p>The grey part under the photo.</p></div></div>
        <div class="card-body form-grid">
          <div class="field full">
            <label for="home_title">Title</label>
            <input id="home_title" name="home_title" class="input" value="{{ old('home_title', setting('home_title', 'Musician')) }}" required>
          </div>
          <div class="field full editor-wrap">
            <label>Text</label>
            <input type="hidden" name="home_text" id="home_text" value="{{ old('home_text', setting('home_text')) }}">
            <div data-editor="home_text" data-placeholder="Write about Jaya…">{!! old('home_text', setting('home_text')) !!}</div>
            <div class="hint">Select words to make them bold or to add a link.</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><div><h3>Top photo</h3><p>Full-screen photo with the name.</p></div></div>
        <div class="card-body form-grid" style="grid-template-columns:1fr">
          <div class="field">
            <img src="{{ media(setting('hero_image', 'assets/img/hero.jpg')) }}" alt="" class="w-100 rounded-3 mb-2" style="aspect-ratio:16/10;object-fit:cover">
            @include('admin.partials.dropzone', ['name' => 'hero_image_file', 'title' => 'Change photo', 'hint' => 'Wide photo, JPG or PNG', 'small' => true])
          </div>
          <div class="field">
            <label for="hero_name">Name on the photo</label>
            <input id="hero_name" name="hero_name" class="input" value="{{ old('hero_name', setting('hero_name', 'Jaya Barman')) }}" required>
          </div>
          <div class="field">
            <label for="hero_tagline">Small line under the name</label>
            <input id="hero_tagline" name="hero_tagline" class="input" value="{{ old('hero_tagline', setting('hero_tagline')) }}">
          </div>
        </div>
      </div>
    </div>

    <div class="mt d-flex justify-content-end">
      <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>Save Home page</button>
    </div>
  </form>
@endsection
