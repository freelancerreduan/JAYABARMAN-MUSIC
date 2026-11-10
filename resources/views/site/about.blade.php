@extends('layouts.site')

@section('title', 'About Me')
@section('body_class', 'page-about')

@section('content')
<div class="about">
    <section class="about-top">
        <div class="row-wrap">
            <div class="row g-4 g-md-5">
                {{-- Left column: photo, then the "left" blocks --}}
                <div class="col-md-6">
                    <img class="about-photo" src="{{ media(setting('about_photo', 'assets/img/portrait.jpg')) }}" alt="Jaya Barman">
                    @foreach ($left as $block)
                        <div class="block">
                            <h2>{{ $block->title }}</h2>
                            {!! $block->body !!}
                        </div>
                    @endforeach
                </div>
                {{-- Right column: the "right" blocks --}}
                <div class="col-md-6">
                    @foreach ($right as $block)
                        <div class="block">
                            <h2>{{ $block->title }}</h2>
                            {!! $block->body !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
