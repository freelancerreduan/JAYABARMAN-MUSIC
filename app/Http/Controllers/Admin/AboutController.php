<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutBlock;
use App\Models\Setting;
use App\Support\Upload;
use Illuminate\Http\Request;

/** About Me: the photo plus text blocks, each placed in the left or right column. */
class AboutController extends Controller
{
    public function index()
    {
        $blocks = AboutBlock::orderBy('sort')->orderBy('id')->get();

        return view('admin.about.index', [
            'left' => $blocks->where('side', 'left'),
            'right' => $blocks->where('side', 'right'),
        ]);
    }

    public function savePhoto(Request $request)
    {
        $request->validate(['about_photo_file' => 'required|image|max:20480'], ['about_photo_file.required' => 'Choose a photo.']);
        Upload::delete(Setting::get('about_photo'));
        Setting::put(['about_photo' => Upload::store($request->file('about_photo_file'), 'about')]);

        return back()->with('success', 'Photo changed.');
    }

    /** "Personal Details" at the top of the right column: simple boxes, no editor. */
    public function saveDetails(Request $request)
    {
        $data = $request->validate([
            'about_details_title' => 'required|string|max:120',
            'about_name' => 'nullable|string|max:150',
            'about_role' => 'nullable|string|max:200',
            'about_email' => 'nullable|email|max:200',
            'about_phone' => 'nullable|string|max:60',
            'about_location' => 'nullable|string|max:150',
            'about_extra' => 'nullable|string|max:2000',
        ]);
        Setting::put($data);

        return back()->with('success', 'Personal Details saved.');
    }

    public function create(Request $request)
    {
        return view('admin.about.form', ['block' => new AboutBlock(['side' => $request->query('side') === 'right' ? 'right' : 'left'])]);
    }

    public function edit(AboutBlock $block)
    {
        return view('admin.about.form', compact('block'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort'] = (AboutBlock::max('sort') ?? 0) + 1;
        AboutBlock::create($data);

        return redirect()->route('admin.about')->with('success', 'Block added.');
    }

    public function update(Request $request, AboutBlock $block)
    {
        $data = $this->validated($request);
        if ($data['side'] !== $block->side) {
            $data['sort'] = (AboutBlock::max('sort') ?? 0) + 1;   // goes to the bottom of the other column
        }
        $block->update($data);

        return redirect()->route('admin.about')->with('success', 'Block saved.');
    }

    public function destroy(AboutBlock $block)
    {
        $block->delete();

        return back()->with('success', 'Block deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'side' => 'required|in:left,right',
            'body' => 'nullable|string',
        ]);
        $data['body'] = clean_html($data['body'] ?? '');

        return $data;
    }
}
