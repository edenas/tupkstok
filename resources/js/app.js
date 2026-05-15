import './scripts/base/base.js';
import {
    Activity,
    AlarmClock,
    AppWindow,
    ArrowRight,
    Bell,
    Blocks,
    Bot,
    Braces,
    Brackets,
    BrainCircuit,
    Clapperboard,
    ChartColumn,
    CircleGauge,
    Clock,
    Code,
    createIcons,
    Database,
    Droplet,
    FileCode,
    FileType,
    Globe,
    Info,
    Layers,
    MonitorSmartphone,
    Megaphone,
    Package,
    Palette,
    PenTool,
    Play,
    Search,
    Share2,
    SlidersHorizontal,
    Smartphone,
    Sparkles,
    TabletSmartphone,
    Target,
    Undo2,
} from 'lucide';
import { initializeLayoutScripts } from './scripts/layouts/layout-scripts.js';
import { initializeComponentScripts } from './scripts/components/component-scripts.js';
import { initializeHeroSectionScripts } from './scripts/sections/section-hero.js';
import { initializeHomePageScripts } from './scripts/pages/page-home.js';
import { initializeMobileAppsPageScripts } from './scripts/pages/page-mobile-apps.js';

initializeLayoutScripts();
initializeComponentScripts();
initializeHeroSectionScripts();
initializeHomePageScripts();
initializeMobileAppsPageScripts();

createIcons({
    icons: {
        Activity,
        AlarmClock,
        AppWindow,
        ArrowRight,
        Bell,
        Blocks,
        Bot,
        Braces,
        Brackets,
        BrainCircuit,
        Clapperboard,
        ChartColumn,
        CircleGauge,
        Clock,
        Code,
        Database,
        Droplet,
        FileCode,
        FileType,
        Globe,
        Info,
        Layers,
        MonitorSmartphone,
        Megaphone,
        Package,
        Palette,
        PenTool,
        Play,
        Search,
        Share2,
        SlidersHorizontal,
        Smartphone,
        Sparkles,
        TabletSmartphone,
        Target,
        Undo2,
    },
});
