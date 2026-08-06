<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'page_key',
    'meta_title_lt',
    'meta_description_lt',
    'keywords_lt',
])]
class SeoSetting extends Model
{
    public const PAGE_KEYS = [
        'global',
        'home',
        'blog',
        'contact',
    ];

    /**
     * Default SEO values used to initialize empty settings without overwriting edits.
     *
     * @return array<string, array<string, string>>
     */
    public static function defaultValues(): array
    {
        return [
            'global' => [
                'meta_title_lt' => 'Tupk Stok',
                'meta_description_lt' => 'Tupk Stok',
                'keywords_lt' => 'Tupk Stok',
            ],
            'home' => [
                'meta_title_lt' => 'Tupk Stok',
                'meta_description_lt' => 'Kuriu modernias interneto svetaines, web aplikacijas ir skaitmeninius produktus, kuriuose vartotojo patirtis, našumas ir vizualinis identitetas veikia kaip viena sistema.',
                'keywords_lt' => 'skaitmeniniai sprendimai, web aplikacijos, interneto svetainės, mobiliosios aplikacijos, grafikos dizainas, SEO optimizacija, technologijos',
            ],
            'about' => [
                'meta_title_lt' => 'Apie mane - Edenas Pocius | Tupk Stok',
                'meta_description_lt' => 'Esu Edenas Pocius - web, mobiliųjų aplikacijų ir skaitmeninių sprendimų kūrėjas, turintis patirties IT, dizaino ir kūrybinių projektų srityse.',
                'keywords_lt' => 'Edenas Pocius, apie mane, Tupk Stok, web kūrėjas, mobiliųjų aplikacijų kūrimas, UI UX, grafikos dizainas, Full Stack',
            ],
            'web_solutions' => [
                'meta_title_lt' => 'Web sprendimai - interneto svetainių ir aplikacijų kūrimas',
                'meta_description_lt' => 'Kuriu modernias, greitas ir funkcionalias interneto svetaines bei web aplikacijas, pritaikytas verslo tikslams, vartotojo patirčiai ir SEO matomumui.',
                'keywords_lt' => 'web sprendimai, interneto svetainių kūrimas, web aplikacijos, UI UX dizainas, SEO integracija, Laravel, PHP, WordPress, responsive dizainas',
            ],
            'mobile_apps' => [
                'meta_title_lt' => 'Mobiliosios aplikacijos - Drink Water programėlė | Tupk Stok',
                'meta_description_lt' => 'Pristatau Drink Water - modernią vandens suvartojimo stebėjimo programėlę, sukurtą su React Native, Expo ir TypeScript technologijomis.',
                'keywords_lt' => 'mobiliosios aplikacijos, Drink Water, React Native, Expo, TypeScript, Android aplikacija, vandens sekimo programėlė, išmanūs priminimai',
            ],
            'blog' => [
                'meta_title_lt' => 'Blog\'as - straipsniai ir naujienos',
                'meta_description_lt' => 'Blog\'e pristatomi Tūpk Stok straipsniai, naujienos, patarimai ir aktualus turinys.',
                'keywords_lt' => 'blogas, straipsniai, naujienos, patarimai, Tūpk Stok',
            ],
            'contact' => [
                'meta_title_lt' => 'Kontaktai - susisiekite dėl web, aplikacijų ir dizaino projektų',
                'meta_description_lt' => 'Susisiekite dėl interneto svetainių, mobiliųjų aplikacijų, animacijos, video projektų ar kūrybinio skaitmeninio darbo.',
                'keywords_lt' => 'kontaktai, susisiekti, web projektai, mobiliosios aplikacijos, dizaino projektai, animacija, Tupk Stok, Edenas Pocius',
            ],
        ];
    }
}
