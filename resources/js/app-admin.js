import Alpine from './alpine-setup';
import './utilities';
import appearanceSettings from './components/appearance-settings';
import bioEditor from './components/bio-editor';
import sidebar from './components/sidebar';

Alpine.data('appearanceSettings', appearanceSettings);
Alpine.data('bioEditor', bioEditor);
Alpine.data('sidebar', sidebar);

Alpine.start();
