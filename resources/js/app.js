import './scripts/base/base.js';
import {
    Activity,
    AppWindow,
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
    FileCode,
    FileType,
    Globe,
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
    Undo2,
} from 'lucide';
import { initializeLayoutScripts } from './scripts/layouts/layout-scripts.js';
import { initializeComponentScripts } from './scripts/components/component-scripts.js';
import { initializeHeroSectionScripts } from './scripts/sections/section-hero.js';
import { initializeHomePageScripts } from './scripts/pages/page-home.js';

initializeLayoutScripts();
initializeComponentScripts();
initializeHeroSectionScripts();
initializeHomePageScripts();

createIcons({
    icons: {
        Activity,
        AppWindow,
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
        FileCode,
        FileType,
        Globe,
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
        Undo2,
    },
});
