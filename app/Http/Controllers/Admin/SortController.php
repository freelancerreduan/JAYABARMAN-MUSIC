<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutBlock;
use App\Models\Album;
use App\Models\GalleryImage;
use App\Models\MusicVideo;
use App\Models\PortfolioGroup;
use App\Models\PortfolioImage;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Saves the order after a drag and drop in the admin. */
class SortController extends Controller
{
    private const MODELS = [
        'albums' => Album::class,
        'tracks' => Track::class,
        'videos' => MusicVideo::class,
        'gallery' => GalleryImage::class,
        'groups' => PortfolioGroup::class,
        'portfolio' => PortfolioImage::class,
        'about' => AboutBlock::class,
    ];

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:'.implode(',', array_keys(self::MODELS)),
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'side' => 'nullable|in:left,right',
        ]);
        $model = self::MODELS[$data['type']];

        DB::transaction(function () use ($data, $model) {
            foreach (array_values($data['ids']) as $i => $id) {
                $values = ['sort' => $i + 1];
                if ($data['type'] === 'about' && ! empty($data['side'])) {
                    $values['side'] = $data['side'];   // block dragged to the other column
                }
                $model::whereKey($id)->update($values);
            }
        });

        return response()->json(['ok' => true]);
    }
}
