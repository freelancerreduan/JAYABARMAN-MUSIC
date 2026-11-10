<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioGroup;
use App\Models\PortfolioImage;
use App\Support\Upload;
use Illuminate\Http\Request;

/** Portfolio: groups (title + many photos). */
class PortfolioController extends Controller
{
    public function index()
    {
        return view('admin.portfolio', [
            'groups' => PortfolioGroup::with('images')->orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $group = PortfolioGroup::create(['title' => $data['title'], 'sort' => (PortfolioGroup::max('sort') ?? 0) + 1]);
        $this->addImages($request, $group);

        return redirect()->to(route('admin.portfolio').'#group-'.$group->id)->with('success', 'Group "'.$group->title.'" created.');
    }

    public function update(Request $request, PortfolioGroup $group)
    {
        $data = $this->validated($request, false);
        $group->update(['title' => $data['title']]);
        $added = $this->addImages($request, $group);

        return redirect()->to(route('admin.portfolio').'#group-'.$group->id)
            ->with('success', $added ? $added.' photo(s) added to "'.$group->title.'".' : 'Group saved.');
    }

    public function destroy(PortfolioGroup $group)
    {
        foreach ($group->images as $image) {
            Upload::delete($image->image);
        }
        $group->delete();

        return back()->with('success', 'Group and its photos deleted.');
    }

    public function destroyImage(PortfolioImage $image)
    {
        $groupId = $image->portfolio_group_id;
        Upload::delete($image->image);
        $image->delete();

        return redirect()->to(route('admin.portfolio').'#group-'.$groupId)->with('success', 'Photo deleted.');
    }

    private function validated(Request $request, bool $new): array
    {
        return $request->validate([
            'title' => 'required|string|max:200',
            'images' => ($new ? 'required' : 'nullable').'|array',
            'images.*' => 'image|max:20480',
        ], [
            'images.required' => 'Choose at least one photo for the new group.',
            'images.*.image' => 'Photos only (JPG, PNG, WEBP).',
        ]);
    }

    private function addImages(Request $request, PortfolioGroup $group): int
    {
        $files = $request->file('images', []);
        $sort = (int) $group->images()->max('sort');
        foreach ($files as $file) {
            $group->images()->create(['image' => Upload::store($file, 'portfolio'), 'sort' => ++$sort]);
        }

        return count($files);
    }
}
