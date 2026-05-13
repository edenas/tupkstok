@extends('layouts.app')

@section('content')
<section class="mobile-app-page">
    <div class="mobile-app-page__container">
        <header class="mobile-app-page__hero">
            <p class="mobile-app-page__eyebrow">Mobiliosios aplikacijos</p>
            <h1 class="mobile-app-page__title">Drink Water</h1>
            <p class="mobile-app-page__lead">
                Moderni vandens suvartojimo stebėjimo programėlė, sukurta padėti formuoti sveiką kasdienį hidratacijos įprotį.
            </p>
        </header>

        <div class="mobile-app-page__layout">
            <article class="mobile-app-page__section mobile-app-page__section--large">
                <h2>Apie aplikaciją</h2>
                <p>
                    Drink Water – moderni vandens suvartojimo stebėjimo programėlė, sukurta padėti formuoti sveiką kasdienį hidratacijos įprotį. Programėlė leidžia patogiai sekti išgeriamo vandens kiekį, gauti išmanius priminimus bei stebėti savo progresą tiek telefone, tiek planšetėje.
                </p>
                <p>
                    Kurdami aplikaciją orientavomės į minimalistinį dizainą, aiškią vartotojo sąsają ir patogų naudojimą kiekvieną dieną. Programėlė pritaikyta įvairiems ekranų dydžiams bei palaiko tiek vertikalų, tiek horizontalų išdėstymą planšetėse ir didesniuose įrenginiuose.
                </p>
            </article>

            <aside class="mobile-app-page__section">
                <h2>Technologijos</h2>
                <ul class="mobile-app-page__tag-list">
                    <li>React Native</li>
                    <li>Expo</li>
                    <li>TypeScript</li>
                    <li>Responsive UI architektūra</li>
                    <li>Android ir iOS palaikymas</li>
                </ul>
            </aside>
        </div>

        <section class="mobile-app-page__section">
            <h2>Pagrindinės funkcijos</h2>
            <ul class="mobile-app-page__feature-grid">
                <li>Vandens suvartojimo sekimas realiu laiku</li>
                <li>Patogus kiekio pasirinkimas su slankikliu</li>
                <li>Vizualus dienos progreso indikatorius</li>
                <li>Statistikos peržiūra</li>
                <li>Išmanūs priminimai apie vandens vartojimą</li>
                <li>Galimybė nustatyti aktyvias priminimų valandas</li>
                <li>„Undo“ funkcija netyčiniams paspaudimams atšaukti</li>
                <li>Pritaikytas dizainas telefonams ir planšetėms</li>
                <li>Modernus, švarus ir lengvai naudojamas UI dizainas</li>
            </ul>
        </section>

        <div class="mobile-app-page__layout">
            <section class="mobile-app-page__section">
                <h2>Išmanūs priminimai</h2>
                <p>
                    Programėlėje integruota lanksti priminimų sistema leidžia vartotojui pasirinkti, kas kiek laiko gauti priminimus ir nuo kada iki kada jie turi veikti.
                </p>
                <p>
                    Tai reiškia, kad pranešimai nevargins vakare ar nakties metu – programėlė prisitaiko prie jūsų dienos ritmo.
                </p>
            </section>

            <section class="mobile-app-page__section">
                <h2>Dizainas ir naudojimo patirtis</h2>
                <p>
                    „Drink Water“ buvo kuriama siekiant suderinti estetiką ir funkcionalumą: švelnios spalvos, modernios kortelės, sklandūs išdėstymai ir adaptyvus dizainas įvairiems ekranams.
                </p>
                <p>
                    Programėlė puikiai veikia tiek telefonuose, tiek planšetėse, o horizontalus režimas pateikia optimizuotą kortelių išdėstymą didesniems ekranams.
                </p>
            </section>
        </div>

        <section class="mobile-app-page__download">
            <div>
                <h2>Atsisiuntimas</h2>
                <p>Nuorodos bus pridėtos vėliau.</p>
            </div>
            <div class="mobile-app-page__download-actions">
                <button type="button" class="mobile-app-page__download-button" disabled>Google Play</button>
                <button type="button" class="mobile-app-page__download-button mobile-app-page__download-button--secondary" disabled>APK version</button>
            </div>
        </section>

        <section class="mobile-app-page__screenshots">
            <div class="mobile-app-page__screenshots-header">
                <h2>Ekrano vaizdai</h2>
                <p>Vietos būsimiems telefono ir planšetės programėlės vaizdams.</p>
            </div>

            <div class="mobile-app-page__screenshot-grid">
                <div class="mobile-app-page__screenshot-placeholder">
                    <span>Phone screenshots</span>
                </div>
                <div class="mobile-app-page__screenshot-placeholder mobile-app-page__screenshot-placeholder--wide">
                    <span>Tablet screenshots</span>
                </div>
            </div>
        </section>
    </div>
</section>
@endsection
