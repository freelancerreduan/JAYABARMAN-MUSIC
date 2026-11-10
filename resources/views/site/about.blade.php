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
                    {{-- Personal Details: simple boxes from the admin --}}
                    @php
                        $extra = collect(preg_split('/\r\n|\r|\n/', (string) setting('about_extra')))->map(fn ($l) => trim($l))->filter();
                    @endphp
                    <div class="block details">
                        <h2>{{ setting('about_details_title', 'Personal Details') }}</h2>
                        @if (setting('about_name') || setting('about_role'))
                            <p>
                                @if (setting('about_name'))Name: <b>{{ setting('about_name') }}</b>@endif
                                @if (setting('about_name') && setting('about_role'))<br>@endif
                                {{ setting('about_role') }}
                            </p>
                        @endif
                        @if (setting('about_email') || setting('about_phone') || setting('about_location') || $extra->isNotEmpty())
                            <p>
                                @if (setting('about_email'))E-Mail: <b>{{ setting('about_email') }}</b><br>@endif
                                @if (setting('about_phone'))Phone: <b>{{ setting('about_phone') }}</b><br>@endif
                                @if (setting('about_location'))Location: <b>{{ setting('about_location') }}</b><br>@endif
                                @foreach ($extra as $line)
                                    @if (str_contains($line, ':'))
                                        {{ Str::before($line, ':') }}: <b>{{ trim(Str::after($line, ':')) }}</b><br>
                                    @else
                                        {{ $line }}<br>
                                    @endif
                                @endforeach
                            </p>
                        @endif
                    </div>

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
