@extends('layouts.site')

@section('title', 'Portfolio')
@section('body_class', 'page-portfolio')

@section('content')
@foreach ($groups as $group)
    {{-- 1st, 3rd, 5th… group: grey band; 2nd, 4th, 6th…: white band --}}
    @php $odd = $loop->odd; @endphp
    <section class="band {{ $odd ? 'band-grey' : 'band-white' }}">
        <div class="row-wrap">
            <h2 class="section-title {{ $odd ? '' : 'light' }}">{{ $group->title }}</h2>
            <div class="media-grid" data-gallery data-per-page="4">
                @foreach ($group->images as $image)
                    <a class="media-item" href="{{ media($image->image) }}" data-lightbox data-src="{{ media($image->image) }}" data-title="{{ $group->title }}"><img src="{{ media($image->image) }}" alt="{{ $group->title }}" loading="lazy"></a>
                @endforeach
            </div>
            <nav class="gallery-pager" aria-label="Gallery pages"></nav>
        </div>
    </section>
@endforeach
@endsection
