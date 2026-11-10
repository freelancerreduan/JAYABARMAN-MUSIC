<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutBlock;
use App\Models\GalleryImage;
use App\Models\MusicVideo;
use App\Models\PortfolioGroup;
use App\Models\PortfolioImage;
use App\Models\Track;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'counts' => [
                'tracks' => Track::count(),
                'videos' => MusicVideo::count(),
                'visual' => GalleryImage::where('section', 'visual-art')->count(),
                'theatre' => GalleryImage::where('section', 'theatre')->count(),
                'groups' => PortfolioGroup::count(),
                'portfolio' => PortfolioImage::count(),
                'blocks' => AboutBlock::count(),
            ],
        ]);
    }
}
