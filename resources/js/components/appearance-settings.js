export default function appearanceSettings(config) {
    return {
        lightTheme: config.currentLight,
        darkTheme: config.currentDark,
        font: config.currentFont,
        savedLight: config.currentLight,
        savedDark: config.currentDark,
        savedFont: config.currentFont,
        fonts: config.fonts,
        lightOpen: false,
        darkOpen: false,
        fontOpen: false,
        previewing: false,

        get resolvedTheme() {
            const mode = localStorage.getItem('themeMode') || 'system';
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (mode === 'dark') return this.darkTheme;
            if (mode === 'light') return this.lightTheme;
            return systemDark ? this.darkTheme : this.lightTheme;
        },

        previewTheme(theme) {
            this.previewing = true;
            document.documentElement.setAttribute('data-theme', theme);
        },

        revertTheme() {
            this.previewing = false;
            document.documentElement.setAttribute('data-theme', this.resolvedTheme);
        },

        selectLight(theme) {
            this.lightTheme = theme;
            this.lightOpen = false;
            this.revertTheme();
        },

        selectDark(theme) {
            this.darkTheme = theme;
            this.darkOpen = false;
            this.revertTheme();
        },

        previewFont(key) {
            this.previewing = true;
            document.documentElement.style.setProperty('--font-sans', 'var(--font-' + key + ')');
        },

        revertFont() {
            this.previewing = false;
            document.documentElement.style.setProperty('--font-sans', 'var(--font-' + this.font + ')');
        },

        selectFont(key) {
            this.font = key;
            this.fontOpen = false;
            this.revertFont();
        },

        init() {
            document.documentElement.setAttribute('data-theme', this.resolvedTheme);
        },
    };
}
