<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MusicVideo extends Model
{
    protected $guarded = [];

    public function isYoutube(): bool
    {
        return $this->type === 'youtube';
    }

    public function thumbnail(): ?string
    {
        return $this->isYoutube() ? 'https://img.youtube.com/vi/'.$this->source.'/hqdefault.jpg' : null;
    }

    /** Accepts a full YouTube link (watch, youtu.be, shorts, embed) or a bare id. */
    public static function youtubeId(string $url): ?string
    {
        $url = trim($url);
        if (preg_match('~^[\w-]{11}$~', $url)) {
            return $url;
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([\w-]{11})~i', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
