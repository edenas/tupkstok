<?php

namespace App\Support;

class BlogCategories
{
    public const DEFAULT = 'Publikacijos';

    public const ALL = [
        'Grožis',
        'Gyvenimo būdas',
        'Mityba',
        'Publikacijos',
        'Sėkmės istorijos',
        'Sportas',
        'Video',
        'Animation',
    ];

    public static function normalize(?string $category): string
    {
        return in_array($category, self::ALL, true) ? $category : self::DEFAULT;
    }
}
