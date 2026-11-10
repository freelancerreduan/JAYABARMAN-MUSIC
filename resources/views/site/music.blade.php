@extends('layouts.site')

@section('title', 'Music')
@section('body_class', 'page-music')

@section('content')
@if ($albums->isNotEmpty())
<section class="band band-grey">
    <div class="row-wrap">
        <h2 class="section-title">Albums</h2>
        <div class="albums">
            @foreach ($albums as $album)
                <div class="player">
                    <div class="player-head">
                        <span class="player-album" role="button" title="Show track list">{{ $album->title }}</span>
                        <a href="{{ media($album->tracks->first()->file) }}" download>download</a>
                        <a href="{{ route('contacts') }}">share</a>
                    </div>
                    <div class="player-by">by Jaya Barman</div>
                    <div class="player-body">
                        <button class="player-play" type="button" aria-label="Play"><i class="bi bi-play-fill"></i></button>
                        <div class="player-info">
                            <div class="player-track"><span class="name"></span><span class="time"></span></div>
                            <div class="player-progress" role="progressbar" aria-label="Progress"><span></span></div>
                            <div class="player-ctrl">
                                <button type="button" data-act="prev" aria-label="Previous track"><i class="bi bi-skip-start-fill"></i></button>
                                <button type="button" data-act="next" aria-label="Next track"><i class="bi bi-skip-end-fill"></i></button>
                            </div>
                        </div>
                    </div>
                    <ul class="player-list">
                        @foreach ($album->tracks as $track)
                            <li data-title="{{ $track->title }}" data-src="{{ media($track->file) }}"><span>{{ $loop->iteration }}. {{ $track->title }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($videos->isNotEmpty())
<section class="band band-black">
    <div class="row-wrap">
        <h2 class="section-title on-black">Music Video</h2>
        <div class="media-grid" data-gallery>
            @foreach ($videos as $video)
                @php $caption = $video->title.($video->subtitle ? ' · '.$video->subtitle : ''); @endphp
                <button type="button" class="media-item is-video" data-lightbox
                        data-type="{{ $video->isYoutube() ? 'youtube' : 'video' }}"
                        data-src="{{ $video->isYoutube() ? $video->source : media($video->source) }}"
                        data-title="{{ $caption }}">
                    @if ($video->isYoutube())
                        <img src="{{ $video->thumbnail() }}" alt="" loading="lazy">
                    @else
                        <video data-src="{{ media($video->source) }}#t=8" preload="none" muted playsinline tabindex="-1"></video>
                    @endif
                    <span class="tile-title">{{ $video->title }}@if ($video->subtitle)<small>{{ $video->subtitle }}</small>@endif</span>
                    <span class="play-badge"><i class="bi bi-play-fill"></i></span>
                </button>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
