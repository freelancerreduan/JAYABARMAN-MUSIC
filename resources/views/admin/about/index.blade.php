@extends('layouts.admin')

@section('title', 'About Me')
@section('subtitle', 'Photo and text blocks in two columns')
@section('view_url', route('about'))

@section('content')
  <div class="page-head">
    <div>
      <h2>About Me</h2>
      <p>The page has two columns. Each block (a heading and its text) goes on the left or the right.</p>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-head"><div><h3>Photo</h3><p>Shown at the top of the left column.</p></div></div>
    <div class="card-body about-photo-row">
      <img src="{{ media(setting('about_photo', 'assets/img/portrait.jpg')) }}" alt="">
      <form method="POST" action="{{ route('admin.about.photo') }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-end" data-busy="Uploading…">
        @csrf
        @method('PUT')
        <div class="flex-grow-1" style="min-width:220px">
          @include('admin.partials.dropzone', ['name' => 'about_photo_file', 'title' => 'Change photo', 'hint' => 'JPG or PNG', 'small' => true])
        </div>
        <button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i>Save photo</button>
      </form>
    </div>
  </div>

  {{-- Personal Details: plain boxes; an empty box hides that line --}}
  <form method="POST" action="{{ route('admin.about.details') }}" class="card mb-4" data-busy="Saving…">
    @csrf
    @method('PUT')
    <div class="card-head"><div><h3><i class="bi bi-person-badge text-primary"></i> Personal Details</h3><p>First block in the right column. Leave a box empty to hide that line.</p></div></div>
    <div class="card-body form-grid">
      <div class="field">
        <label for="d_title">Heading</label>
        <input id="d_title" name="about_details_title" class="input" value="{{ old('about_details_title', setting('about_details_title', 'Personal Details')) }}" required>
      </div>
      <div class="field">
        <label for="d_name">Name</label>
        <input id="d_name" name="about_name" class="input" value="{{ old('about_name', setting('about_name')) }}" placeholder="JAYA BARMAN">
      </div>
      <div class="field">
        <label for="d_role">Work / role</label>
        <input id="d_role" name="about_role" class="input" value="{{ old('about_role', setting('about_role')) }}" placeholder="Actor, Musician">
      </div>
      <div class="field">
        <label for="d_email">E-mail</label>
        <input id="d_email" name="about_email" type="email" @class(['input', 'is-invalid' => $errors->has('about_email')]) value="{{ old('about_email', setting('about_email')) }}">
        @error('about_email')<div class="error">{{ $message }}</div>@enderror
      </div>
      <div class="field">
        <label for="d_phone">Phone <span class="form-label-sm">(optional)</span></label>
        <input id="d_phone" name="about_phone" class="input" value="{{ old('about_phone', setting('about_phone')) }}">
      </div>
      <div class="field">
        <label for="d_loc">Location <span class="form-label-sm">(optional)</span></label>
        <input id="d_loc" name="about_location" class="input" value="{{ old('about_location', setting('about_location')) }}" placeholder="Paris, France">
      </div>
      <div class="field full">
        <label for="d_extra">More lines <span class="form-label-sm">(optional)</span></label>
        <textarea id="d_extra" name="about_extra" class="textarea" rows="3" placeholder="One per line, e.g.&#10;Nationality: Bangladeshi&#10;Languages: Bangla, English, French">{{ old('about_extra', setting('about_extra')) }}</textarea>
        <div class="hint">One line each. Write “Label: value” and the value is shown in bold.</div>
      </div>
    </div>
    <div class="card-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>Save Personal Details</button></div>
  </form>

  <div class="help"><i class="bi bi-arrows-move"></i><div>Other blocks: drag a block up or down, or into the other column. The page changes the same way.</div></div>

  <div class="about-cols">
    @foreach (['left' => $left, 'right' => $right] as $side => $blocks)
      <div class="card about-col" data-empty-wrap>
        <div class="card-head">
          <div><h3><i class="bi bi-layout-sidebar{{ $side === 'right' ? '-reverse' : '' }}"></i> {{ ucfirst($side) }} column</h3><p>{{ $side === 'left' ? 'Under the photo' : 'Next to the photo' }}</p></div>
          <a href="{{ route('admin.about.create', ['side' => $side]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i>Add block</a>
        </div>
        <ul class="block-list {{ $blocks->isEmpty() ? 'is-empty' : '' }}" data-sortable="about" data-side="{{ $side }}" data-group="about" data-handle=".drag-handle">
          @foreach ($blocks as $block)
            <li class="block-item" data-id="{{ $block->id }}">
              <span class="drag-handle" title="Drag to move"><i class="bi bi-grip-vertical"></i></span>
              <div class="grow">
                <strong>{{ $block->title }}</strong>
                <small>{{ Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['</li>', '</p>', '<br>'], [' · ', ' ', ' '], $block->body)))), 140) ?: 'No text' }}</small>
              </div>
              <div class="acts">
                <a href="{{ route('admin.about.edit', $block) }}" class="btn btn-sm btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                @include('admin.partials.delete', ['action' => route('admin.about.destroy', $block), 'confirm' => 'Delete the block "'.$block->title.'"?'])
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </div>
@endsection
