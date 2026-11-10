@extends('layouts.site')

@section('title', 'Home')
@section('body_class', 'page-home')

@section('content')
<!-- Hero: photo on black, name and tagline on top -->
<section class="hero" aria-label="{{ setting('hero_name', 'Jaya Barman') }}" style="--hero-img:url('{{ media(setting('hero_image', 'assets/img/hero.jpg')) }}')">
    <div class="hero-text">
        <p class="hero-name">{{ setting('hero_name', 'Jaya Barman') }}</p>
        @if (setting('hero_tagline'))
            <p class="hero-tagline">{!! strip_tags(setting('hero_tagline'), '<b>') !!}</p>
        @endif
    </div>
</section>

<!-- Grey band with the introduction -->
<section class="band-grey intro">
    <div class="row-wrap">
        <h1>{{ setting('home_title', 'Musician') }}</h1>
        <div class="intro-text">{!! setting('home_text') !!}</div>
    </div>
</section>
@endsection
