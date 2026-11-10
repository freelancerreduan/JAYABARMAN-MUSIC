<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    protected $guarded = [];

    public const SECTIONS = [
        'visual-art' => 'Visual Art',
        'theatre' => 'Actor (on stage)',
    ];
}
