@extends('layouts.admin')

@section('title', $label)
@section('subtitle', '“'.$heading.'” photos')
@section('view_url', route($section))

@section('content')
  <div class="page-head">
    <div>
      <h2>{{ $heading }}</h2>
      <p>Upload photos, delete them, or drag them to change the order on the {{ $label }} page.</p>
    </div>
  </div>

  <form method="POST" action="{{ route('admin.gallery.store', $section) }}" enctype="multipart/form-data" class="card mb-4" data-busy="Uploading…">
    @csrf
    <div class="card-body">
      @include('admin.partials.dropzone', ['name' => 'images', 'multiple' => true, 'title' => 'Drag photos here or click to choose', 'hint' => 'You can pick many photos at once · JPG, PNG, WEBP'])
      <div class="d-flex justify-content-end mt-3">
        <button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i>Upload photos</button>
      </div>
    </div>
  </form>

  <div class="card">
    <div class="card-head"><div><h3>{{ $images->count() }} photos</h3><p>Drag a photo to move it. Number 1 is shown first.</p></div></div>
    @if ($images->isEmpty())
      <div class="empty"><i class="bi bi-images"></i>No photos yet. Upload some above.</div>
    @else
      <div class="card-body">
        <div class="sort-grid" data-sortable="gallery">
          @foreach ($images as $image)
            <div class="sort-card" data-id="{{ $image->id }}">
              <img src="{{ media($image->image) }}" alt="" loading="lazy">
              <span class="no" data-no>{{ $loop->iteration }}</span>
              <div class="del">
                @include('admin.partials.delete', ['action' => route('admin.gallery.destroy', $image), 'confirm' => 'Delete this photo?'])
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>
@endsection
