import Alpine from 'alpinejs';
import ajax from '@imacrayon/alpine-ajax';
import collapse from '@alpinejs/collapse';
import morph from '@alpinejs/morph';
import persist from '@alpinejs/persist';
import demoCountdown from './components/demo-countdown';

Alpine.plugin(morph);
Alpine.plugin(ajax);
Alpine.plugin(collapse);
Alpine.plugin(persist);

Alpine.data('demoCountdown', demoCountdown);

window.Alpine = Alpine;

export default Alpine;
