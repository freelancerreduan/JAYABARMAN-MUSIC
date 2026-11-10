@extends('layouts.admin')

@section('title', $video->exists ? 'Edit video' : 'Add video')
@section('subtitle', 'Music page · Music Video')
@section('view_url', route('music'))

@php $type = old('type', $video->type ?: 'youtube'); @endphp

@section('content')
  <form method="POST" action="{{ $video->exists ? route('admin.videos.update', $video) : route('admin.videos.store') }}" enctype="multipart/form-data" class="card" style="max-width:760px" data-busy="Saving… (a big video can take a while)">
    @csrf
    @if ($video->exists) @method('PUT') @endif

    <div class="card-head"><div><h3>{{ $video->exists ? 'Edit video' : 'New video' }}</h3><p>Choose a YouTube link or upload a video file.</p></div></div>

    <div class="card-body form-grid" data-type-switch>
      <div class="field">
        <label for="v_title">Title</label>
        <input id="v_title" name="title" @class(['input', 'is-invalid' => $errors->has('title')]) value="{{ old('title', $video->title) }}" required>
      </div>
      <div class="field">
        <label for="v_sub">Small line under the title <span class="form-label-sm">(optional)</span></label>
        <input id="v_sub" name="subtitle" class="input" value="{{ old('subtitle', $video->subtitle) }}" placeholder="e.g. Jaya Barman">
      </div>

      <div class="field full">
        <span class="label">Video from</span>
        <div class="choice">
          <label><input type="radio" name="type" value="youtube" @checked($type === 'youtube')><span><i class="bi bi-youtube"></i>YouTube link</span></label>
          <label><input type="radio" name="type" value="file" @checked($type === 'file')><span><i class="bi bi-upload"></i>Upload a file</span></label>
        </div>
      </div>

      <div class="field full" data-when="youtube">
        <label for="v_yt">YouTube link</label>
        <input id="v_yt" name="youtube" @class(['input', 'is-invalid' => $errors->has('youtube')]) value="{{ old('youtube', $video->isYoutube() && $video->source ? 'https://www.youtube.com/watch?v='.$video->source : '') }}" placeholder="https://www.youtube.com/watch?v=…">
        @error('youtube')<div class="error">{{ $message }}</div>@enderror
        <div class="hint">Copy the link from the browser address bar or from YouTube's Share button.</div>
      </div>

      <div class="field full" data-when="file">
        <label>Video file</label>
        @if ($video->exists && $video->type === 'file')
          <video src="{{ media($video->source) }}" controls preload="metadata" class="w-100 rounded-3 mb-2" style="max-height:260px;background:#000"></video>
          <div class="hint mb-2">Upload a new file only to replace this one.</div>
        @endif
        @include('admin.partials.dropzone', ['name' => 'file', 'accept' => 'video/mp4,video/webm,video/ogg,video/quicktime', 'title' => 'Drag a video here or click', 'hint' => 'MP4 works best'])
        @error('file')<div class="error">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="card-foot">
      <a href="{{ route('admin.music') }}#videos" class="btn">Cancel</a>
      <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>{{ $video->exists ? 'Save video' : 'Add video' }}</button>
    </div>
  </form>
@endsection
