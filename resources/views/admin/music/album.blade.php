@extends('layouts.admin')

@section('title', $album->title)
@section('subtitle', 'Music · Album')
@section('view_url', route('music'))

@section('content')
  <div class="page-head">
    <div>
      <a href="{{ route('admin.music') }}" class="back-link"><i class="bi bi-arrow-left"></i>All albums</a>
      <h2>{{ $album->title }}</h2>
      <p>{{ $album->tracks->count() }} {{ Str::plural('song', $album->tracks->count()) }} · shown as one player on the Music page.</p>
    </div>
    <div class="actions">
      @include('admin.partials.delete', ['action' => route('admin.albums.destroy', $album), 'confirm' => 'Delete the album "'.$album->title.'" and all its songs?', 'btnLabel' => 'Delete album'])
    </div>
  </div>

  <div class="album-layout">
    {{-- The songs --}}
    <div class="card">
      <div class="card-head"><div><h3><i class="bi bi-music-note-list text-primary"></i> Songs</h3><p>Drag <i class="bi bi-grip-vertical"></i> to change the order. Click a name to rename it.</p></div></div>
      @if ($album->tracks->isEmpty())
        <div class="empty"><i class="bi bi-music-note"></i>No songs yet. Upload them with Add songs.</div>
      @else
        <ul class="song-list" data-sortable="tracks" data-handle=".drag-handle">
          @foreach ($album->tracks as $track)
            <li class="song" data-id="{{ $track->id }}">
              <span class="drag-handle" title="Drag to move"><i class="bi bi-grip-vertical"></i></span>
              <span class="no" data-no>{{ $loop->iteration }}</span>
              <button type="button" class="song-play" data-song="{{ media($track->file) }}" title="Play" aria-label="Play {{ $track->title }}"><i class="bi bi-play-fill"></i></button>
              <form method="POST" action="{{ route('admin.tracks.update', $track) }}" class="song-name">
                @csrf
                @method('PUT')
                <input name="title" class="input" value="{{ $track->title }}" aria-label="Song name" required>
                <button class="btn btn-sm btn-icon" type="submit" title="Save name"><i class="bi bi-check2"></i></button>
              </form>
              @include('admin.partials.delete', ['action' => route('admin.tracks.destroy', $track), 'confirm' => 'Delete the song "'.$track->title.'"?'])
            </li>
          @endforeach
        </ul>
      @endif
    </div>
    {{-- Side: name + upload --}}
    <div class="album-side">
      <form method="POST" action="{{ route('admin.albums.update', $album) }}" class="card">
        @csrf
        @method('PUT')
        <div class="card-head"><div><h3><i class="bi bi-vinyl text-primary"></i> Album name</h3></div></div>
        <div class="card-body">
          <div class="field">
            <input name="title" class="input" value="{{ $album->title }}" aria-label="Album name" required>
          </div>
        </div>
        <div class="card-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>Save name</button></div>
      </form>

      <form method="POST" action="{{ route('admin.tracks.store', $album) }}" enctype="multipart/form-data" class="card" data-busy="Uploading…">
        @csrf
        <div class="card-head"><div><h3><i class="bi bi-upload text-primary"></i> Add songs</h3><p>Pick one or many audio files.</p></div></div>
        <div class="card-body form-grid one">
          <div class="field">
            <label>Audio file</label>
            @include('admin.partials.dropzone', ['name' => 'files', 'multiple' => true, 'accept' => 'audio/*,.mp3,.m4a,.wav,.ogg,.aac,.flac', 'title' => 'Drag MP3 files here or click', 'hint' => 'MP3, M4A, WAV, OGG', 'small' => true])
          </div>
          <div class="field">
            <label for="t_title">Song name <span class="form-label-sm">(optional)</span></label>
            <input id="t_title" name="title" class="input" placeholder="Empty = use the file name">
            <div class="hint">Only used when you upload one file.</div>
          </div>
        </div>
        <div class="card-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i>Upload</button></div>
      </form>
    </div>

  </div>
@endsection
