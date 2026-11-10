@extends('layouts.admin')

@section('title', 'Music')
@section('subtitle', 'Albums with songs, and the Music Video grid')
@section('view_url', route('music'))

@section('content')
  {{-- ================= Albums ================= --}}
  <div class="page-head">
    <div>
      <h2>Albums</h2>
      <p>Each album is one player on the Music page. Add songs to an album by uploading audio files.</p>
    </div>
  </div>

  <form method="POST" action="{{ route('admin.albums.store') }}" class="card mb-4">
    @csrf
    <div class="card-body d-flex flex-wrap gap-2 align-items-end">
      <div class="field flex-grow-1" style="min-width:220px">
        <label for="new_album">New album name</label>
        <input id="new_album" name="title" class="input" placeholder="e.g. Bengali Folk" required>
      </div>
      <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i>Add album</button>
    </div>
  </form>

  @if ($albums->isEmpty())
    <div class="card"><div class="empty"><i class="bi bi-vinyl"></i>No albums yet. Add one above.</div></div>
  @else
    <div class="help"><i class="bi bi-arrows-move"></i><div>Drag <i class="bi bi-grip-vertical"></i> to change the order of albums or songs. It is saved right away.</div></div>
  @endif

  <div data-sortable="albums" data-handle=".album-handle">
    @foreach ($albums as $album)
      <div class="card album-card" data-id="{{ $album->id }}">
        <div class="card-head">
          <span class="drag-handle album-handle" title="Drag to move"><i class="bi bi-grip-vertical"></i></span>
          <form method="POST" action="{{ route('admin.albums.update', $album) }}" class="album-name">
            @csrf
            @method('PUT')
            <i class="bi bi-vinyl text-secondary"></i>
            <input name="title" class="input" value="{{ $album->title }}" aria-label="Album name" required>
            <button class="btn btn-sm" type="submit" title="Save name"><i class="bi bi-check2"></i>Save</button>
          </form>
          <span class="badge">{{ $album->tracks->count() }} songs</span>
          @include('admin.partials.delete', ['action' => route('admin.albums.destroy', $album), 'confirm' => 'Delete the album "'.$album->title.'" and all its songs?', 'label' => 'Album'])
        </div>

        <ul class="sort-rows" data-sortable="tracks" data-handle=".drag-handle">
          @forelse ($album->tracks as $track)
            <li class="sort-row" data-id="{{ $track->id }}">
              <span class="drag-handle" title="Drag to move"><i class="bi bi-grip-vertical"></i></span>
              <span class="no" data-no>{{ $loop->iteration }}</span>
              <form method="POST" action="{{ route('admin.tracks.update', $track) }}" class="rename">
                @csrf
                @method('PUT')
                <input name="title" class="input" value="{{ $track->title }}" aria-label="Song name" required>
                <button class="btn btn-sm btn-icon" type="submit" title="Save name"><i class="bi bi-check2"></i></button>
              </form>
              <audio src="{{ media($track->file) }}" controls preload="none"></audio>
              <div class="acts">
                @include('admin.partials.delete', ['action' => route('admin.tracks.destroy', $track), 'confirm' => 'Delete the song "'.$track->title.'"?'])
              </div>
            </li>
          @empty
            <li class="sort-row text-secondary"><i class="bi bi-info-circle"></i> No songs yet. Upload below.</li>
          @endforelse
        </ul>

        <form method="POST" action="{{ route('admin.tracks.store', $album) }}" enctype="multipart/form-data" class="album-add" data-busy="Uploading…">
          @csrf
          <div class="row-fields">
            <div class="field">
              <label for="t_title_{{ $album->id }}">Song name</label>
              <input id="t_title_{{ $album->id }}" name="title" class="input" placeholder="Leave empty to use the file name">
            </div>
            <div class="field">
              <label>Audio file</label>
              @include('admin.partials.dropzone', ['name' => 'files', 'multiple' => true, 'accept' => 'audio/*,.mp3,.m4a,.wav,.ogg,.aac,.flac', 'title' => 'Drag MP3 files here or click', 'small' => true])
            </div>
            <button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i>Upload song</button>
          </div>
        </form>
      </div>
    @endforeach
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
