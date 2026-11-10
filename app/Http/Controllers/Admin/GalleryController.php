<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Support\Upload;
use Illuminate\Http\Request;

/** Visual Art and Theatre: upload photos, delete, drag to order. */
class GalleryController extends Controller
{
    public function index(string $section)
    {
        abort_unless(isset(GalleryImage::SECTIONS[$section]), 404);

        return view('admin.gallery', [
            'section' => $section,
            'label' => $section === 'theatre' ? 'Theatre' : 'Visual Art',
            'heading' => GalleryImage::SECTIONS[$section],
            'images' => GalleryImage::where('section', $section)->orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request, string $section)
    {
        abort_unless(isset(GalleryImage::SECTIONS[$section]), 404);
        $request->validate(['images' => 'required|array|min:1', 'images.*' => 'image|max:20480'], [
            'images.required' => 'Choose at least one photo.',
            'images.*.image' => 'Photos only (JPG, PNG, WEBP).',
        ]);

        $sort = (int) GalleryImage::where('section', $section)->max('sort');
        foreach ($request->file('images') as $file) {
            GalleryImage::create(['section' => $section, 'image' => Upload::store($file, $section), 'sort' => ++$sort]);
        }

        return back()->with('success', count($request->file('images')).' photo(s) uploaded.');
    }

    public function destroy(GalleryImage $image)
    {
        Upload::delete($image->image);
        $image->delete();

        return back()->with('success', 'Photo deleted.');
    }
}
