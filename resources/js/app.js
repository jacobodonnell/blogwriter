import './bootstrap';

import Alpine from 'alpinejs';
import ajax from '@imacrayon/alpine-ajax';

Alpine.plugin(ajax);

window.Alpine = Alpine;
Alpine.start();
