@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Pick a page to change it')
@section('view_url', route('home'))

@php
  $cards = [
    ['admin.home', [], 'house-door', 'blue', 'Home', 'Photo, name, title and text'],
    ['admin.music', [], 'music-note-beamed', 'violet', 'Music', $counts['tracks'].' songs · '.$counts['videos'].' videos'],
    ['admin.gallery', ['section' => 'visual-art'], 'palette', 'pink', 'Visual Art', $counts['visual'].' photos'],
    ['admin.gallery', ['section' => 'theatre'], 'mask', 'orange', 'Theatre', $counts['theatre'].' photos'],
    ['admin.portfolio', [], 'grid-3x3-gap', 'teal', 'Portfolio', $counts['groups'].' groups · '.$counts['portfolio'].' photos'],
    ['admin.about', [], 'person-vcard', 'green', 'About Me', 'Photo and '.$counts['blocks'].' text blocks'],
    ['admin.contacts', [], 'envelope-paper', 'red', 'Contacts', 'Photo, location, WhatsApp, email'],
    ['admin.settings', [], 'gear', 'blue', 'Footer links & login', 'Social links, email and password'],
  ];
@endphp

@section('content')
  <div class="page-head">
    <div>
      <h2>Hello, {{ auth()->user()->name }}</h2>
      <p>Each box is one page of the website. Open it to change what visitors see.</p>
    </div>
    <div class="actions">
      <a href="{{ route('home') }}" target="_blank" class="btn"><i class="bi bi-globe2"></i>Open website</a>
    </div>
  </div>

  <div class="dash-pages">
    @foreach ($cards as [$route, $params, $icon, $tone, $label, $sub])
      <a href="{{ route($route, $params) }}" class="card dash-page tone-{{ $tone }}">
        <span class="mini-icon"><i class="bi bi-{{ $icon }}"></i></span>
        <div><strong>{{ $label }}</strong><span>{{ $sub }}</span></div>
        <i class="bi bi-chevron-right"></i>
      </a>
    @endforeach
  </div>

  <div class="help mt"><i class="bi bi-arrows-move"></i><div><b>Tip:</b> on every page with photos, songs or videos you can drag the items to change their order. The website shows them in the same order.</div></div>
@endsection
