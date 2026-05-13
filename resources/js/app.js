import './scripts/base/base.js';
import {
    AppWindow,
    Blocks,
    Bot,
    Braces,
    BrainCircuit,
    Clapperboard,
    Code,
    createIcons,
    Database,
    FileCode,
    FileType,
    Globe,
    MonitorSmartphone,
    Megaphone,
    Palette,
    PenTool,
    Play,
    Search,
    Share2,
    Smartphone,
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
        AppWindow,
        Blocks,
        Bot,
        Braces,
        BrainCircuit,
        Clapperboard,
        Code,
        Database,
        FileCode,
        FileType,
        Globe,
        MonitorSmartphone,
        Megaphone,
        Palette,
        PenTool,
        Play,
        Search,
        Share2,
        Smartphone,
    },
});
