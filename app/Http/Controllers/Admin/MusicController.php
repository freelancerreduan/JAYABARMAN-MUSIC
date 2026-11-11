<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\MusicVideo;
use App\Models\Track;
use App\Support\Upload;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    public function index()
    {
        return view('admin.music.index', [
            'albums' => Album::with('tracks')->orderBy('sort')->orderBy('id')->get(),
            'videos' => MusicVideo::orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    // ---------- Albums ----------
    public function storeAlbum(Request $request)
    {
        $data = $request->validate(['title' => 'required|string|max:200']);
        $album = Album::create($data + ['sort' => (Album::max('sort') ?? 0) + 1]);

        return redirect()->route('admin.albums.edit', $album)->with('success', 'Album added. Now upload its songs.');
    }

    public function editAlbum(Album $album)
    {
        $album->load('tracks');

        return view('admin.music.album', compact('album'));
    }

    public function updateAlbum(Request $request, Album $album)
    {
        $album->update($request->validate(['title' => 'required|string|max:200']));

        return back()->with('success', 'Album name saved.');
    }

    public function destroyAlbum(Album $album)
    {
        foreach ($album->tracks as $track) {
            Upload::delete($track->file);
        }
        $album->delete();

        return redirect()->route('admin.music')->with('success', 'Album and its songs deleted.');
    }

    // ---------- Songs (audio) ----------
    public function storeTracks(Request $request, Album $album)
    {
        $request->validate([
            'title' => 'nullable|string|max:200',
            'files' => 'required|array|min:1',
            'files.*' => 'file|max:204800|mimes:mp3,m4a,wav,ogg,aac,flac,mp4,webm,mpga,oga',
        ], [
            'files.required' => 'Choose at least one audio file.',
            'files.*.mimes' => 'Audio files only: MP3, M4A, WAV, OGG, AAC or FLAC.',
        ]);

        $files = $request->file('files');
        $sort = (int) $album->tracks()->max('sort');
        foreach ($files as $file) {
            $title = count($files) === 1 && $request->filled('title') ? $request->input('title') : Upload::titleFrom($file);
            $album->tracks()->create([
                'title' => $title,
                'file' => Upload::store($file, 'audio'),
                'sort' => ++$sort,
            ]);
        }

        return back()->with('success', count($files) === 1 ? 'Song uploaded.' : count($files).' songs uploaded.');
    }

    public function updateTrack(Request $request, Track $track)
    {
        $track->update($request->validate(['title' => 'required|string|max:200']));

        return back()->with('success', 'Song name saved.');
    }

    public function destroyTrack(Track $track)
    {
        Upload::delete($track->file);
        $track->delete();

        return back()->with('success', 'Song deleted.');
    }

    // ---------- Music videos ----------
    public function createVideo()
    {
        return view('admin.music.video', ['video' => new MusicVideo(['type' => 'youtube'])]);
    }

    public function editVideo(MusicVideo $video)
    {
        return view('admin.music.video', compact('video'));
    }

    public function storeVideo(Request $request)
    {
        $video = new MusicVideo(['sort' => (MusicVideo::max('sort') ?? 0) + 1]);
        $this->fillVideo($request, $video);

        return redirect()->to(route('admin.music').'#videos')->with('success', 'Video added.');
    }

    public function updateVideo(Request $request, MusicVideo $video)
    {
        $this->fillVideo($request, $video);

        return redirect()->to(route('admin.music').'#videos')->with('success', 'Video saved.');
    }

    public function destroyVideo(MusicVideo $video)
    {
        if (! $video->isYoutube()) {
            Upload::delete($video->source);
        }
        $video->delete();

        return back()->with('success', 'Video deleted.');
    }

    private function fillVideo(Request $request, MusicVideo $video): void
    {
        $hasFile = $video->exists && $video->type === 'file';
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:200',
            'type' => 'required|in:youtube,file',
            'youtube' => 'required_if:type,youtube|nullable|string|max:300',
            'file' => [$hasFile ? 'nullable' : 'required_if:type,file', 'nullable', 'file', 'max:2097152', 'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime'],
        ], [
            'youtube.required_if' => 'Paste the YouTube link.',
            'file.required_if' => 'Choose a video file.',
            'file.mimetypes' => 'Video files only: MP4, WEBM, OGG or MOV.',
        ]);

        $old = $video->type === 'file' ? $video->source : null;

        if ($data['type'] === 'youtube') {
            $id = MusicVideo::youtubeId($data['youtube']);
            if (! $id) {
                throw \Illuminate\Validation\ValidationException::withMessages(['youtube' => 'This is not a YouTube link. Copy it from the browser address bar or the Share button.']);
            }
            $source = $id;
            Upload::delete($old);
        } elseif ($request->hasFile('file')) {
            $source = Upload::store($request->file('file'), 'video');
            Upload::delete($old);
        } else {
            $source = $old;
        }

        $video->fill([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'type' => $data['type'],
            'source' => $source,
        ])->save();
    }
}
