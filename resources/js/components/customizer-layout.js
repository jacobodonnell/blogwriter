export default function customizerLayout() {
    return {
        drawerOpen: JSON.parse(localStorage.getItem('customizerDrawerOpen') ?? 'true'),
        classicEditor: JSON.parse(localStorage.getItem('customizerClassicEditor') ?? 'false'),
        panelWidth: parseInt(localStorage.getItem('customizerWidth')) || 480,
        previewWidth: parseInt(localStorage.getItem('customizerPreviewWidth')) || 0,
        dragging: false,
        dragTarget: null,
        startX: 0,
        saved: false,

        init() {
            this.$watch('drawerOpen', v => localStorage.setItem('customizerDrawerOpen', JSON.stringify(v)));
            this.$watch('classicEditor', v => localStorage.setItem('customizerClassicEditor', JSON.stringify(v)));
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

        closeDrawer() {
            if (this.classicEditor) {
                this.classicEditor = false;
            }
            this.drawerOpen = false;
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
