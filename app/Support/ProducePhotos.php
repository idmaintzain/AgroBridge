<?php

namespace App\Support;

class ProducePhotos
{
    public static function url(?string $crop): string
    {
        $key = strtolower(trim((string) $crop));
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $key), '-');
        $file = public_path('images/'.$slug.'.jpg');

        if ($slug === '' || ! is_file($file)) {
            return asset('images/fallback.svg');
        }

        return asset('images/'.$slug.'.jpg');
    }

    public static function hero(): string
    {
        return asset('images/farmer-hero.jpg');
    }
}
