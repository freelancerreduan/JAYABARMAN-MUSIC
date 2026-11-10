@extends('layouts.admin')

@section('title', 'Portfolio')
@section('subtitle', 'Photo groups, each with its own title')
@section('view_url', route('portfolio'))

@section('content')
  <div class="page-head">
    <div>
      <h2>Portfolio groups</h2>
      <p>Each group is one section on the Portfolio page: a title and its photos. Colours alternate: 1st grey, 2nd white, 3rd grey…</p>
    </div>
  </div>

  {{-- New group --}}
  <form method="POST" action="{{ route('admin.portfolio.store') }}" enctype="multipart/form-data" class="card mb-4" data-busy="Uploading…">
    @csrf
    <div class="card-head"><div><h3><i class="bi bi-plus-circle text-primary"></i> New group</h3><p>Give it a title and pick its photos.</p></div></div>
    <div class="card-body form-grid">
      <div class="field">
        <label for="g_title">Group title</label>
        <input id="g_title" name="title" class="input" value="{{ old('title') }}" placeholder="e.g. Instruments" required>
      </div>
      <div class="field">
        <label>Photos</label>
        @include('admin.partials.dropzone', ['name' => 'images', 'multiple' => true, 'title' => 'Drag photos here or click', 'hint' => 'Pick as many as you like', 'small' => true])
      </div>
    </div>
    <div class="card-foot"><button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i>Create group</button></div>
  </form>

  @if ($groups->isNotEmpty())
    <div class="help"><i class="bi bi-arrows-move"></i><div>Drag <i class="bi bi-grip-vertical"></i> to move a whole group. Drag a photo to move it inside its group. Changes are saved right away.</div></div>
  @endif

  <div data-sortable="groups" data-handle=".group-handle">
    @foreach ($groups as $group)
      @php $formId = 'group-form-'.$group->id; @endphp
      <div class="card group-card {{ $loop->odd ? 'odd' : 'even' }}" id="group-{{ $group->id }}" data-id="{{ $group->id }}">
        <form method="POST" action="{{ route('admin.portfolio.update', $group) }}" enctype="multipart/form-data" id="{{ $formId }}" data-busy="Saving…">
          @csrf
          @method('PUT')
        </form>

        <div class="card-head">
          <div class="group-head">
            <span class="drag-handle group-handle" title="Drag to move the group"><i class="bi bi-grip-vertical"></i></span>
            <span class="no" data-no>{{ $loop->iteration }}</span>
            <input name="title" form="{{ $formId }}" class="input" value="{{ $group->title }}" aria-label="Group title" required style="height:38px;font-weight:600">
          </div>
          <span class="badge">{{ $group->images->count() }} photos</span>
          @include('admin.partials.delete', ['action' => route('admin.portfolio.destroy', $group), 'confirm' => 'Delete the group "'.$group->title.'" and all its photos?', 'btnLabel' => 'Group'])
        </div>

        <div class="card-body">
          @if ($group->images->isEmpty())
            <div class="empty py-3"><i class="bi bi-images"></i>No photos in this group.</div>
          @else
            <div class="sort-grid" data-sortable="portfolio">
              @foreach ($group->images as $image)
                <div class="sort-card" data-id="{{ $image->id }}">
                  <img src="{{ media($image->image) }}" alt="" loading="lazy">
                  <span class="no" data-no>{{ $loop->iteration }}</span>
                  <div class="del">
                    @include('admin.partials.delete', ['action' => route('admin.portfolio.image.destroy', $image), 'confirm' => 'Delete this photo?'])
                  </div>
                </div>
              @endforeach
            </div>
          @endif

          <div class="mt-3">
            @include('admin.partials.dropzone', ['name' => 'images', 'multiple' => true, 'form' => $formId, 'title' => 'Add more photos to "'.$group->title.'"', 'small' => true])
          </div>
        </div>
        <div class="card-foot">
          <button class="btn btn-primary" type="submit" form="{{ $formId }}"><i class="bi bi-check2"></i>Save group</button>
        </div>
      </div>
    @endforeach
  </div>
@endsection
