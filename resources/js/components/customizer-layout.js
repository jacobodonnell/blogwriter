export default function customizerLayout() {
    return {
        drawerOpen: JSON.parse(localStorage.getItem('customizerDrawerOpen') ?? 'true'),
        panelWidth: parseInt(localStorage.getItem('customizerWidth')) || 480,
        previewWidth: parseInt(localStorage.getItem('customizerPreviewWidth')) || 0,
        dragging: false,
        dragTarget: null,
        startX: 0,
        saved: false,

        init() {
            this.$watch('drawerOpen', v => localStorage.setItem('customizerDrawerOpen', JSON.stringify(v)));
            this.$watch('panelWidth', w => localStorage.setItem('customizerWidth', w));
            this.$watch('previewWidth', w => localStorage.setItem('customizerPreviewWidth', w));
        },

        get previewAreaWidth() {
            const drawer = this.drawerOpen ? this.panelWidth + 8 : 0;
            return window.innerWidth - drawer;
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
    };
}
