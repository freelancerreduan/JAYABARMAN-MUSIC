@extends('layouts.admin')

@section('title', 'Music')
@section('subtitle', 'Albums with songs, and the Music Video grid')
@section('view_url', route('music'))

@section('content')
  {{-- ================= Albums ================= --}}
  <div class="page-head">
    <div>
      <h2>Albums</h2>
      <p>Each album is one player on the Music page. Open an album to add, rename or remove its songs.</p>
    </div>
    <form method="POST" action="{{ route('admin.albums.store') }}" class="actions album-new">
      @csrf
      <input name="title" class="input" placeholder="New album name" aria-label="New album name" required>
      <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i>Add album</button>
    </form>
  </div>

  <div class="card mb-2">
    @if ($albums->isEmpty())
      <div class="empty"><i class="bi bi-vinyl"></i>No albums yet. Type a name above and press Add album.</div>
    @else
      <div class="card-body">
        <div class="sort-grid album-grid" data-sortable="albums">
          @foreach ($albums as $album)
            <div class="sort-card album-tile" data-id="{{ $album->id }}">
              <a href="{{ route('admin.albums.edit', $album) }}" class="album-cover no-drag" title="Open album">
                <i class="bi bi-vinyl-fill"></i>
                <span>{{ $album->tracks->count() }} {{ Str::plural('song', $album->tracks->count()) }}</span>
              </a>
              <span class="no" data-no>{{ $loop->iteration }}</span>
              <div class="cap">
                <i class="bi bi-music-note-list text-primary"></i>
                <div class="grow">
                  <div class="truncate fw-semibold" title="{{ $album->title }}">{{ $album->title }}</div>
                  <small class="truncate">{{ $album->tracks->count() }} {{ Str::plural('song', $album->tracks->count()) }}</small>
                </div>
                <a href="{{ route('admin.albums.edit', $album) }}" class="btn btn-sm btn-icon no-drag" title="Open album"><i class="bi bi-pencil"></i></a>
                @include('admin.partials.delete', ['action' => route('admin.albums.destroy', $album), 'confirm' => 'Delete the album "'.$album->title.'" and all its songs?'])
              </div>
            </div>
          @endforeach
        </div>
        <div class="hint mt-3"><i class="bi bi-arrows-move"></i> Drag the cards to change the order. Click a card to open the album.</div>
      </div>
    @endif
  </div>

  {{-- ================= Music videos ================= --}}
  <div class="page-head mt-5" id="videos">
    <div>
      <h2>Music Video</h2>
      <p>YouTube links or uploaded video files. Drag the cards to change the order.</p>
    </div>
    <div class="actions">
      <a href="{{ route('admin.videos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>Add video</a>
    </div>
  </div>

  <div class="card">
    @if ($videos->isEmpty())
      <div class="empty"><i class="bi bi-film"></i>No videos yet.</div>
    @else
      <div class="card-body">
        <div class="sort-grid" data-sortable="videos">
          @foreach ($videos as $video)
            <div class="sort-card" data-id="{{ $video->id }}">
              @if ($video->isYoutube())
                <img src="{{ $video->thumbnail() }}" alt="" loading="lazy">
              @else
                <video src="{{ media($video->source) }}#t=8" preload="metadata" muted playsinline></video>
              @endif
              <span class="no" data-no>{{ $loop->iteration }}</span>
              <div class="cap">
                <i class="bi {{ $video->isYoutube() ? 'bi-youtube text-danger' : 'bi-film text-primary' }}" title="{{ $video->isYoutube() ? 'YouTube' : 'Uploaded file' }}"></i>
                <div class="grow">
                  <div class="truncate fw-semibold">{{ $video->title }}</div>
                  <small class="truncate">{{ $video->subtitle ?: ($video->isYoutube() ? 'YouTube' : 'Video file') }}</small>
                </div>
                <a href="{{ route('admin.videos.edit', $video) }}" class="btn btn-sm btn-icon no-drag" title="Edit"><i class="bi bi-pencil"></i></a>
                @include('admin.partials.delete', ['action' => route('admin.videos.destroy', $video), 'confirm' => 'Delete the video "'.$video->title.'"?'])
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>
@endsection
