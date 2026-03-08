const MOBILE_BREAKPOINT = 768;

export default function customizerLayout(serverDefaults = {}) {
    const serverMode = serverDefaults.editorMode ?? 'fullscreen';
    const lastServerMode = localStorage.getItem('customizerServerEditorMode');
    const serverModeChanged = lastServerMode !== null && lastServerMode !== serverMode;

    if (serverModeChanged) {
        localStorage.removeItem('customizerMode');
    }

    localStorage.setItem('customizerServerEditorMode', serverMode);

    return {
        mode: serverModeChanged ? serverMode : (localStorage.getItem('customizerMode') ?? serverMode),
        panelWidth: parseInt(localStorage.getItem('customizerWidth')) || 480,
        previewWidth: parseInt(localStorage.getItem('customizerPreviewWidth')) || 0,
        windowWidth: window.innerWidth,
        dragging: false,
        dragTarget: null,
        startX: 0,
        saved: false,
        _preFullscreenMode: null,

        get isMobile() {
            return this.windowWidth < MOBILE_BREAKPOINT;
        },

        get hasPreview() {
            return this.mode === 'split' || this.mode === 'preview';
        },

        init() {
            this.$watch('mode', v => localStorage.setItem('customizerMode', v));
            this.$watch('panelWidth', w => localStorage.setItem('customizerWidth', w));
            this.$watch('previewWidth', w => localStorage.setItem('customizerPreviewWidth', w));

            window.addEventListener('resize', () => {
                this.windowWidth = window.innerWidth;
            });
        },

        get previewAreaWidth() {
            const drawer = this.mode !== 'preview' ? this.panelWidth + 8 : 0;
            return this.windowWidth - drawer;
        },

        setPreset(w) {
            this.previewWidth = w;
        },

        startDrag(target, event) {
            this.dragging = true;
            this.dragTarget = target;
            this.startX = event.clientX;
            document.body.style.userSelect = 'none';
            document.body.style.cursor = 'col-resize';
        },

        handleDrag(event) {
            if (!this.dragging) return;

            const delta = event.clientX - this.startX;
            this.startX = event.clientX;

            if (this.dragTarget === 'drawer') {
                this.panelWidth = Math.min(700, Math.max(320, this.panelWidth + delta));
            } else if (this.dragTarget === 'preview-left') {
                this.previewWidth = Math.max(320, Math.min(this.previewAreaWidth, this.previewWidth - delta));
            } else if (this.dragTarget === 'preview-right') {
                this.previewWidth = Math.max(320, Math.min(this.previewAreaWidth, this.previewWidth + delta));
            }
        },

        stopDrag() {
            if (!this.dragging) return;

            this.dragging = false;
            this.dragTarget = null;
            document.body.style.userSelect = '';
            document.body.style.cursor = '';
        },
    };
}
