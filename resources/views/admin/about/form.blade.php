@extends('layouts.admin')

@section('title', $block->exists ? 'Edit block' : 'Add block')
@section('subtitle', 'About Me page')
@section('view_url', route('about'))


@section('content')
  <form method="POST" action="{{ $block->exists ? route('admin.about.update', $block) : route('admin.about.store') }}" class="card" style="max-width:860px">
    @csrf
    @if ($block->exists) @method('PUT') @endif

    <div class="card-head"><div><h3>{{ $block->exists ? $block->title : 'New block' }}</h3><p>A heading and its text, e.g. “Awards” with a list.</p></div></div>

    <div class="card-body form-grid">
      <div class="field">
        <label for="b_title">Heading</label>
        <input id="b_title" name="title" @class(['input', 'is-invalid' => $errors->has('title')]) value="{{ old('title', $block->title) }}" placeholder="e.g. Awards" required>
      </div>
      <div class="field">
        <label for="b_side">Column</label>
        <select id="b_side" name="side" class="select">
          <option value="left" @selected(old('side', $block->side) === 'left')>Left (under the photo)</option>
          <option value="right" @selected(old('side', $block->side) === 'right')>Right (next to the photo)</option>
        </select>
      </div>
      <div class="field full editor-wrap">
        <label>Text</label>
        <input type="hidden" name="body" id="b_body" value="{{ old('body', $block->body) }}">
        <div data-editor="b_body" data-placeholder="Write here. Use the list buttons for a list, B for bold.">{!! old('body', $block->body) !!}</div>
        <div class="hint">Tip: make a line <b>bold</b> for a sub-heading, like “Founder Member”. Press Enter for a new line.</div>
      </div>
    </div>

    <div class="card-foot">
      <a href="{{ route('admin.about') }}" class="btn">Cancel</a>
      <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i>{{ $block->exists ? 'Save block' : 'Add block' }}</button>
    </div>
  </form>
@endsection
