export default function uploadPhotoModal() {
    return {
        uploadPreview: null,

        handleFileChange(event) {
            const file = event.target.files[0];
            if (!file) return;

            const maxKB = parseInt(event.target.dataset.maxSizeKb) || 10240;
            const maxSize = maxKB * 1024;
            const maxMB = (maxKB / 1024).toFixed(maxKB % 1024 === 0 ? 0 : 1);
            if (file.size > maxSize) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                this.$dispatch('toast:show', {
                    message: `Image is too large (${sizeMB} MB). Maximum size is ${maxMB} MB.`,
                    type: 'error',
                });
                event.target.value = '';
                this.uploadPreview = null;
                this.$root.closest('dialog')?.close();
                return;
            }

            if (this.uploadPreview) {
                URL.revokeObjectURL(this.uploadPreview);
            }
            this.uploadPreview = URL.createObjectURL(file);
        },
    };
}
