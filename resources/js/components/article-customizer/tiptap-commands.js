export function makeTiptapCommands(getEditor) {
    return {
        showLinkDialog: false,
        linkUrl: '',
        showImageDialog: false,
        editingImage: false,
        imageUrl: '',
        imageAlt: '',
        imageCaption: '',
        showYoutubeDialog: false,
        youtubeUrl: '',

        command(name) {
            const editor = getEditor();
            if (!editor) return;
            const signal = editor.command(name);
            if (signal === 'link') {
                this.linkUrl = '';
                this.showLinkDialog = true;
            } else if (signal === 'image') {
                if (editor.isActive('image')) {
                    this.openEditImage();
                } else {
                    this.resetImageDialog();
                    this.showImageDialog = true;
                }
            } else if (signal === 'youtube') {
                this.youtubeUrl = '';
                this.showYoutubeDialog = true;
            }
        },

        isActive(name, attrs = {}) {
            void this.updatedAt;
            return getEditor()?.isActive(name, attrs) ?? false;
        },

        insertLink() {
            if (!this.linkUrl) return;
            getEditor().setLink({ href: this.linkUrl });
            this.showLinkDialog = false;
        },

        openEditImage() {
            const editor = getEditor();
            if (!editor || !editor.isActive('image')) return;
            const attrs = editor.getAttributes('image');
            this.imageUrl     = attrs.src     ?? '';
            this.imageAlt     = attrs.alt     ?? '';
            this.imageCaption = attrs.caption ?? '';
            this.editingImage = true;
            this.showImageDialog = true;
        },

        insertImage() {
            if (!this.imageUrl) return;
            const editor = getEditor();
            const attrs = {
                src:     this.imageUrl,
                alt:     this.imageAlt     || undefined,
                caption: this.imageCaption || undefined,
            };
            if (this.editingImage) {
                editor.updateImageAttributes(attrs);
            } else {
                editor.setImage(attrs);
            }
            this.showImageDialog = false;
            this.resetImageDialog();
        },

        isImageFullWidth() {
            void this.updatedAt;
            return getEditor()?.getAttributes('image')?.width == null;
        },

        resetImageDialog() {
            this.imageUrl     = '';
            this.imageAlt     = '';
            this.imageCaption = '';
            this.editingImage = false;
        },

        insertYoutube() {
            if (!this.youtubeUrl) return;
            getEditor().setYoutubeVideo({ src: this.youtubeUrl });
            this.showYoutubeDialog = false;
        },
    };
}
