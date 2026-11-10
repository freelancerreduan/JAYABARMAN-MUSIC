<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/** Files go straight into public/uploads so the site works on any host without storage:link. */
class Upload
{
    public static function store(UploadedFile $file, string $folder): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $name = Str::limit($name, 40, '').'-'.Str::lower(Str::random(6)).'.'.$ext;
        $file->move(public_path('uploads/'.$folder), $name);

        return 'uploads/'.$folder.'/'.$name;
    }

    /** Deletes only files the admin uploaded, never the bundled assets/. */
    public static function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/') && ! str_contains($path, '..')) {
            $full = public_path($path);
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }

    /** Title from a file name: "ami-tor_piriter.mp3" -> "Ami tor piriter" */
    public static function titleFrom(UploadedFile $file): string
    {
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return Str::ucfirst(trim(preg_replace('/[-_]+/', ' ', $base)));
    }
}
