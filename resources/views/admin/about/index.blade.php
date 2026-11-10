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

  <div class="help"><i class="bi bi-arrows-move"></i><div>Drag a block up or down, or into the other column. The page changes the same way.</div></div>

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
