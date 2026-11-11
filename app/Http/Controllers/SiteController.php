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

    /** The public website pages, for robots.txt and sitemap.xml. */
    private array $publicRoutes = ['home', 'music', 'visual-art', 'theatre', 'portfolio', 'about', 'contacts'];

    /** Search engines allowed while "Let search engines show this website" is on in admin › SEO. */
    private function indexingAllowed(): bool
    {
        try {
            return (string) setting('seo_index', '1') !== '0';
        } catch (\Throwable) {
            return false;   // database not ready (e.g. before install): stay closed to crawlers
        }
    }

    public function robots()
    {
        $lines = ['User-agent: *'];
        if ($this->indexingAllowed()) {
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Sitemap: '.url('/sitemap.xml');
        } else {
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n", 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function sitemap()
    {
        $urls = '';
        if ($this->indexingAllowed()) {
            foreach ($this->publicRoutes as $name) {
                $urls .= '  <url><loc>'.e(route($name)).'</loc><changefreq>weekly</changefreq></url>'."\n";
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".$urls.'</urlset>'."\n";

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
