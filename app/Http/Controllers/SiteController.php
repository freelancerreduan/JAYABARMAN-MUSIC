<?php

namespace App\Http\Controllers;

use App\Models\AboutBlock;
use App\Models\Album;
use App\Models\GalleryImage;
use App\Models\MusicVideo;
use App\Models\PortfolioGroup;

class SiteController extends Controller
{
    public function home()
    {
        return view('site.home');
    }

    public function music()
    {
        return view('site.music', [
            'albums' => Album::with('tracks')->orderBy('sort')->orderBy('id')->get()->filter(fn ($a) => $a->tracks->isNotEmpty()),
            'videos' => MusicVideo::orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    public function visualArt()
    {
        return $this->gallery('visual-art', 'band-dark', 'on-dark');
    }

    public function theatre()
    {
        return $this->gallery('theatre', 'band-theatre', 'on-black');
    }

    private function gallery(string $section, string $band, string $titleClass)
    {
        return view('site.gallery', [
            'images' => GalleryImage::where('section', $section)->orderBy('sort')->orderBy('id')->get(),
            'heading' => GalleryImage::SECTIONS[$section],
            'band' => $band,
            'titleClass' => $titleClass,
            'section' => $section,
        ]);
    }

    public function portfolio()
    {
        return view('site.portfolio', [
            'groups' => PortfolioGroup::with('images')->orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    public function about()
    {
        $blocks = AboutBlock::orderBy('sort')->orderBy('id')->get();

        return view('site.about', [
            'left' => $blocks->where('side', 'left'),
            'right' => $blocks->where('side', 'right'),
        ]);
    }

    public function contacts()
    {
        return view('site.contacts');
    }
}
