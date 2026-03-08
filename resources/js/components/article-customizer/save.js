export function makeSave(config, getEditor) {
    return {
        submitFullSave() {
            const editor = getEditor();
            if (editor) {
                this.content = editor.getMarkdown();
            }
            if (!this.content?.trim()) {
                this.contentError = true;
                this.$dispatch('toast:show', { message: 'Please add some content before saving.', type: 'error' });
                editor?.focus();
                return;
            }
            this.hasChanges = false;
            const form = this.$refs.customizerForm;
            form.removeAttribute('x-target');
            form.action = config.saveRoute;
            const removing = !this.selectedPhotoId && !this.featuredImageUrl && !this.hasNewPhoto;
            this.$refs.removeFeaturedImageInput.value = removing ? '1' : '0';
            if (!this.hasNewPhoto) {
                form.enctype = 'application/x-www-form-urlencoded';
            }
            form.submit();
        },
    };
}
