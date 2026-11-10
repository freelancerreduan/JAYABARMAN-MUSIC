<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioImage extends Model
{
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(PortfolioGroup::class, 'portfolio_group_id');
    }
}
