<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Upload;
use Illuminate\Http\Request;

/** Pages that hold only a few single values: Home, Contacts, Settings. */
class PageController extends Controller
{
    public function home()
    {
        return view('admin.home');
    }

    public function saveHome(Request $request)
    {
        $data = $request->validate([
            'hero_name' => 'required|string|max:120',
            'hero_tagline' => 'nullable|string|max:200',
            'home_title' => 'required|string|max:200',
            'home_text' => 'nullable|string',
            'hero_image_file' => 'nullable|image|max:20480',
        ]);
        $data['home_text'] = clean_html($data['home_text'] ?? '');
        $this->replaceImage($request, $data, 'hero_image', 'home');
        Setting::put($data);

        return back()->with('success', 'Home page saved.');
    }

    public function contacts()
    {
        return view('admin.contacts');
    }

    public function saveContacts(Request $request)
    {
        $data = $request->validate([
            'contact_title' => 'required|string|max:120',
            'contact_location' => 'nullable|string|max:200',
            'contact_whatsapp' => 'nullable|string|max:200',
            'contact_email' => 'nullable|email|max:200',
            'contact_image_file' => 'nullable|image|max:20480',
        ]);
        $this->replaceImage($request, $data, 'contact_image', 'contacts');
        Setting::put($data);

        return back()->with('success', 'Contacts page saved.');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'social_facebook' => 'nullable|url|max:300',
            'social_instagram' => 'nullable|url|max:300',
            'social_spotify' => 'nullable|url|max:300',
            'social_youtube' => 'nullable|url|max:300',
            'social_whatsapp' => 'nullable|url|max:300',
        ]);
        Setting::put($data);

        return back()->with('success', 'Footer links saved.');
    }

    public function saveAccount(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:200|unique:users,email,'.$user->id,
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('success', 'Login details saved.');
    }

    /** A new file replaces the old one (an old upload is deleted). */
    private function replaceImage(Request $request, array &$data, string $key, string $folder): void
    {
        unset($data[$key.'_file']);
        if ($request->hasFile($key.'_file')) {
            Upload::delete(Setting::get($key));
            $data[$key] = Upload::store($request->file($key.'_file'), $folder);
        }
    }
}
