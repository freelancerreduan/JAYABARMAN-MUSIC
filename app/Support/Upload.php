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
        self::shrink(public_path('uploads/'.$folder.'/'.$name), $ext);

        return 'uploads/'.$folder.'/'.$name;
    }

    /** Big phone photos are made smaller (max 1920px wide/high) so pages load fast. */
    private static function shrink(string $path, string $ext, int $max = 1920): void
    {
        if (! function_exists('imagecreatefromjpeg') || ! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return;
        }
        $size = @getimagesize($path);
        if (! $size || max($size[0], $size[1]) <= $max) {
            return;
        }
        $src = match ($ext) {
            'png' => @imagecreatefrompng($path),
            'webp' => @imagecreatefromwebp($path),
            default => @imagecreatefromjpeg($path),
        };
        if (! $src) {
            return;
        }
        $ratio = $max / max($size[0], $size[1]);
        $w = (int) round($size[0] * $ratio);
        $h = (int) round($size[1] * $ratio);
        $dst = imagecreatetruecolor($w, $h);
        if ($ext !== 'jpg' && $ext !== 'jpeg') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);
        match ($ext) {
            'png' => imagepng($dst, $path, 7),
            'webp' => imagewebp($dst, $path, 82),
            default => imagejpeg($dst, $path, 84),
        };
        imagedestroy($src);
        imagedestroy($dst);
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
