@extends('layouts.site')

@section('title', 'Contacts')
@section('body_class', 'page-contacts')

@php
    $wa = setting('contact_whatsapp');
    // A bare number becomes a wa.me link; a full link is used as it is
    if ($wa && ! preg_match('~^https?://~', $wa)) {
        $wa = 'https://wa.me/'.preg_replace('/\D+/', '', $wa);
    }
@endphp

@section('content')
<section class="band band-dark">
    <div class="row-wrap">
        <img class="contact-photo" src="{{ media(setting('contact_image', 'assets/img/jaya-live.jpg')) }}" alt="Jaya Barman">
        <h2 class="contact-title">{{ setting('contact_title', 'Get in Touch') }}</h2>
        <div class="contact-lines">
            @if (setting('contact_location'))
                <p>Location: <strong>{{ setting('contact_location') }}</strong></p>
            @endif
            @if ($wa)
                <p>WhatsApp: <a href="{{ $wa }}" target="_blank" rel="noopener">Message on WhatsApp</a></p>
            @endif
            @if (setting('contact_email'))
                <p>E-Mail: <a href="mailto:{{ setting('contact_email') }}">{{ setting('contact_email') }}</a></p>
            @endif
        </div>
    </div>
</section>
@endsection
