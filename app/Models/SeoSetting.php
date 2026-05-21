<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'page_key',
    'meta_title_lt',
    'meta_description_lt',
    'keywords_lt',
    'meta_title_en',
    'meta_description_en',
    'keywords_en',
    'meta_title_ru',
    'meta_description_ru',
    'keywords_ru',
])]
class SeoSetting extends Model
{
    public const PAGE_KEYS = [
        'global',
        'home',
        'about',
        'web_solutions',
        'mobile_apps',
        'graphics',
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
                'meta_title_lt' => 'EPgalerija – WEB, mobiliosios aplikacijos ir dizaino sprendimai',
                'meta_description_lt' => 'EPgalerija kuria modernias interneto svetaines, mobiliąsias aplikacijas, grafikos dizaino, animacijos ir skaitmeninius sprendimus verslui.',
                'keywords_lt' => 'EPgalerija, interneto svetainių kūrimas, web sprendimai, mobiliosios aplikacijos, grafikos dizainas, animacija, UI UX, SEO, Laravel, React Native',
                'meta_title_en' => 'EPgalerija – Web, Mobile App and Design Solutions',
                'meta_description_en' => 'EPgalerija creates modern websites, mobile applications, graphic design, animation and digital solutions for businesses and creative projects.',
                'keywords_en' => 'EPgalerija, web development, web solutions, mobile apps, graphic design, animation, UI UX, SEO, Laravel, React Native',
                'meta_title_ru' => 'EPgalerija – веб, мобильные приложения и дизайн-решения',
                'meta_description_ru' => 'EPgalerija создает современные сайты, мобильные приложения, графический дизайн, анимацию и цифровые решения для бизнеса.',
                'keywords_ru' => 'EPgalerija, создание сайтов, веб-решения, мобильные приложения, графический дизайн, анимация, UI UX, SEO, Laravel, React Native',
            ],
            'home' => [
                'meta_title_lt' => 'EPgalerija – skaitmeniniai sprendimai, kurie kuria vertę',
                'meta_description_lt' => 'Kuriu modernias interneto svetaines, web aplikacijas ir skaitmeninius produktus, kuriuose vartotojo patirtis, našumas ir vizualinis identitetas veikia kaip viena sistema.',
                'keywords_lt' => 'skaitmeniniai sprendimai, web aplikacijos, interneto svetainės, mobiliosios aplikacijos, grafikos dizainas, SEO optimizacija, technologijos',
                'meta_title_en' => 'EPgalerija – Digital Solutions That Create Value',
                'meta_description_en' => 'I create modern websites, web applications and digital products where user experience, performance and visual identity work as one system.',
                'keywords_en' => 'digital solutions, web applications, websites, mobile apps, graphic design, SEO optimization, technologies',
                'meta_title_ru' => 'EPgalerija – цифровые решения, создающие ценность',
                'meta_description_ru' => 'Я создаю современные сайты, веб-приложения и цифровые продукты, где пользовательский опыт, производительность и визуальная идентичность работают как единая система.',
                'keywords_ru' => 'цифровые решения, веб-приложения, сайты, мобильные приложения, графический дизайн, SEO оптимизация, технологии',
            ],
            'about' => [
                'meta_title_lt' => 'Apie mane – Edenas Pocius | EPgalerija',
                'meta_description_lt' => 'Esu Edenas Pocius – web, mobiliųjų aplikacijų ir skaitmeninių sprendimų kūrėjas, turintis patirties IT, dizaino ir kūrybinių projektų srityse.',
                'keywords_lt' => 'Edenas Pocius, apie mane, EPgalerija, web kūrėjas, mobiliųjų aplikacijų kūrimas, UI UX, grafikos dizainas, Full Stack',
                'meta_title_en' => 'About Me – Edenas Pocius | EPgalerija',
                'meta_description_en' => 'I am Edenas Pocius, a web, mobile app and digital solutions developer with experience across IT, design and creative projects.',
                'keywords_en' => 'Edenas Pocius, about me, EPgalerija, web developer, mobile app development, UI UX, graphic design, Full Stack',
                'meta_title_ru' => 'Обо мне – Edenas Pocius | EPgalerija',
                'meta_description_ru' => 'Я Edenas Pocius, разработчик веб-сайтов, мобильных приложений и цифровых решений с опытом в IT, дизайне и творческих проектах.',
                'keywords_ru' => 'Edenas Pocius, обо мне, EPgalerija, веб-разработчик, разработка мобильных приложений, UI UX, графический дизайн, Full Stack',
            ],
            'web_solutions' => [
                'meta_title_lt' => 'Web sprendimai – interneto svetainių ir aplikacijų kūrimas',
                'meta_description_lt' => 'Kuriu modernias, greitas ir funkcionalias interneto svetaines bei web aplikacijas, pritaikytas verslo tikslams, vartotojo patirčiai ir SEO matomumui.',
                'keywords_lt' => 'web sprendimai, interneto svetainių kūrimas, web aplikacijos, UI UX dizainas, SEO integracija, Laravel, PHP, WordPress, responsive dizainas',
                'meta_title_en' => 'Web Solutions – Website and Web App Development',
                'meta_description_en' => 'I create modern, fast and functional websites and web applications tailored to business goals, user experience and SEO visibility.',
                'keywords_en' => 'web solutions, website development, web applications, UI UX design, SEO integration, Laravel, PHP, WordPress, responsive design',
                'meta_title_ru' => 'Веб-решения – разработка сайтов и веб-приложений',
                'meta_description_ru' => 'Я создаю современные, быстрые и функциональные сайты и веб-приложения, адаптированные под бизнес-цели, UX и SEO-видимость.',
                'keywords_ru' => 'веб-решения, создание сайтов, веб-приложения, UI UX дизайн, SEO интеграция, Laravel, PHP, WordPress, адаптивный дизайн',
            ],
            'mobile_apps' => [
                'meta_title_lt' => 'Mobiliosios aplikacijos – Drink Water programėlė | EPgalerija',
                'meta_description_lt' => 'Pristatau Drink Water – modernią vandens suvartojimo stebėjimo programėlę, sukurtą su React Native, Expo ir TypeScript technologijomis.',
                'keywords_lt' => 'mobiliosios aplikacijos, Drink Water, React Native, Expo, TypeScript, Android aplikacija, vandens sekimo programėlė, išmanūs priminimai',
                'meta_title_en' => 'Mobile Apps – Drink Water App | EPgalerija',
                'meta_description_en' => 'Introducing Drink Water, a modern water intake tracking app built with React Native, Expo and TypeScript technologies.',
                'keywords_en' => 'mobile apps, Drink Water, React Native, Expo, TypeScript, Android app, water tracking app, smart reminders',
                'meta_title_ru' => 'Мобильные приложения – Drink Water | EPgalerija',
                'meta_description_ru' => 'Представляю Drink Water, современное приложение для отслеживания потребления воды, созданное с React Native, Expo и TypeScript.',
                'keywords_ru' => 'мобильные приложения, Drink Water, React Native, Expo, TypeScript, Android приложение, трекер воды, умные напоминания',
            ],
            'graphics' => [
                'meta_title_lt' => 'Grafika – animacija, vizualizacijos ir kūrybiniai projektai',
                'meta_description_lt' => 'Grafikos skiltyje pristatomi animacijos, motion graphics, vizualizacijų, reklaminių dizainų ir kūrybinių skaitmeninių projektų darbai.',
                'keywords_lt' => 'grafika, animacija, motion graphics, vizualizacijos, reklamos dizainas, kūrybiniai projektai, video animacija, EPgalerija',
                'meta_title_en' => 'Graphics – Animation, Visuals and Creative Projects',
                'meta_description_en' => 'The graphics section presents animation, motion graphics, visualizations, advertising design and creative digital project work.',
                'keywords_en' => 'graphics, animation, motion graphics, visualizations, advertising design, creative projects, video animation, EPgalerija',
                'meta_title_ru' => 'Графика – анимация, визуализации и творческие проекты',
                'meta_description_ru' => 'В разделе графики представлены анимация, motion graphics, визуализации, рекламный дизайн и творческие цифровые проекты.',
                'keywords_ru' => 'графика, анимация, motion graphics, визуализации, рекламный дизайн, творческие проекты, видеоанимация, EPgalerija',
            ],
            'contact' => [
                'meta_title_lt' => 'Kontaktai – susisiekite dėl web, aplikacijų ir dizaino projektų',
                'meta_description_lt' => 'Susisiekite dėl interneto svetainių, mobiliųjų aplikacijų, animacijos, video projektų ar kūrybinio skaitmeninio darbo.',
                'keywords_lt' => 'kontaktai, susisiekti, web projektai, mobiliosios aplikacijos, dizaino projektai, animacija, EPgalerija, Edenas Pocius',
                'meta_title_en' => 'Contact – Web, App and Design Project Inquiries',
                'meta_description_en' => 'Get in touch for website development, mobile applications, animation, video projects or creative digital work.',
                'keywords_en' => 'contact, get in touch, web projects, mobile applications, design projects, animation, EPgalerija, Edenas Pocius',
                'meta_title_ru' => 'Контакты – запросы по веб, приложениям и дизайну',
                'meta_description_ru' => 'Свяжитесь со мной по вопросам разработки сайтов, мобильных приложений, анимации, видео-проектов или творческой цифровой работы.',
                'keywords_ru' => 'контакты, связаться, веб-проекты, мобильные приложения, дизайн-проекты, анимация, EPgalerija, Edenas Pocius',
            ],
        ];
    }
}
