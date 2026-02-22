import Alpine from './alpine-setup';
import './utilities';
import appearanceSettings from './components/appearance-settings';
import sidebar from './components/sidebar';

Alpine.data('appearanceSettings', appearanceSettings);
Alpine.data('sidebar', sidebar);

Alpine.start();
