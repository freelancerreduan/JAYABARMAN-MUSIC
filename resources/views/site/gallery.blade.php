@extends('layouts.site')

@section('title', $section === 'theatre' ? 'Theatre' : 'Visual Art')
@section('body_class', 'page-'.$section)

@section('content')
<section class="band {{ $band }}">
    <div class="row-wrap">
        <h2 class="section-title {{ $titleClass }}">{{ $heading }}</h2>
        <div class="media-grid" data-gallery>
            @foreach ($images as $image)
                <a class="media-item" href="{{ media($image->image) }}" data-lightbox data-src="{{ media($image->image) }}" data-title=""><img src="{{ media($image->image) }}" alt="{{ $heading }}" loading="lazy"></a>
            @endforeach
        </div>
    </div>
</section>
@endsection
