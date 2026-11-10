<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $guarded = [];

    public function tracks()
    {
        return $this->hasMany(Track::class)->orderBy('sort')->orderBy('id');
    }
}
