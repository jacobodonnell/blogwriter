import Alpine from 'alpinejs';
import ajax from '@imacrayon/alpine-ajax';
import persist from '@alpinejs/persist';
import sidebar from './components/sidebar';

Alpine.plugin(ajax);
Alpine.plugin(persist);
Alpine.data('sidebar', sidebar);

window.Alpine = Alpine;
Alpine.start();

// Honor Laravel's @method('PUT/PATCH/DELETE') directive in Alpine AJAX requests
document.addEventListener('ajax:send', (event) => {
    if (event.detail.body instanceof FormData && event.detail.body.has('_method')) {
        event.detail.method = event.detail.body.get('_method').toUpperCase();
    }
});

// Service worker registration
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}

// Dynamic theme-color sync with DaisyUI base-100
function updateThemeColor() {
    const meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) return;

    const temp = document.createElement('div');
    temp.className = 'bg-base-100';
    temp.style.position = 'absolute';
    temp.style.visibility = 'hidden';
    document.body.appendChild(temp);
    const rgb = getComputedStyle(temp).backgroundColor;
    document.body.removeChild(temp);

    const match = rgb.match(/\d+/g);
    if (match) {
        const hex = '#' + match.slice(0, 3).map(x => (+x).toString(16).padStart(2, '0')).join('');
        meta.setAttribute('content', hex);
    }
}

// Update on page load
updateThemeColor();

// Watch for data-theme changes (dark mode toggle)
const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
        if (mutation.attributeName === 'data-theme') {
            updateThemeColor();
        }
    }
});

observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
