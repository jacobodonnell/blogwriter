export function makeTiptapCommands(getEditor) {
    return {
        showLinkDialog: false,
        linkUrl: '',
        showImageDialog: false,
        editingImage: false,
        imageUrl: '',
        imageAlt: '',
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
                if (editor.isActive('figure')) {
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
            if (!editor || !editor.isActive('figure')) return;
            const attrs = editor.getAttributes('figure');
            this.imageUrl     = attrs.src ?? '';
            this.imageAlt     = attrs.alt ?? '';
            this.editingImage = true;
            this.showImageDialog = true;
        },

        insertImage() {
            if (!this.imageUrl) return;
            const editor = getEditor();
            const attrs = {
                src: this.imageUrl,
                alt: this.imageAlt || undefined,
            };
            if (this.editingImage) {
                editor.updateFigureAttributes(attrs);
            } else {
                editor.setFigure(attrs);
            }
            this.showImageDialog = false;
            this.resetImageDialog();
        },

        removeImage() {
            const editor = getEditor();
            if (!editor || !editor.isActive('figure')) return;
            editor.command('removeFigure');
            this.showImageDialog = false;
            this.resetImageDialog();
        },

        isImageFullWidth() {
            void this.updatedAt;
            return getEditor()?.getAttributes('figure')?.width == null;
        },

        resetImageDialog() {
            this.imageUrl     = '';
            this.imageAlt     = '';
            this.editingImage = false;
        },

        insertYoutube() {
            if (!this.youtubeUrl) return;
            getEditor().setYoutubeVideo({ src: this.youtubeUrl });
            this.showYoutubeDialog = false;
        },

        // --- Hybrid undo/redo: ProseMirror first, snapshot fallback ---

        canUndo() {
            void this.updatedAt;
            const editor = getEditor();
            if (editor?.canUndo()) return true;
            return this.historyPointer > 0;
        },

        canRedo() {
            void this.updatedAt;
            const editor = getEditor();
            if (editor?.canRedo()) return true;
            return this.historyPointer < this.contentHistory.length;
        },

        undo() {
            const editor = getEditor();
            if (editor?.canUndo()) {
                editor.undo();
                return;
            }
            if (this.historyPointer <= 0) return;

            // Before stepping back, save current content as the "future" entry if needed
            if (this.historyPointer === this.contentHistory.length) {
                this._snapshotHead = this.content;
            }

            this.historyPointer--;
            const snapshot = this.contentHistory[this.historyPointer];
            this.content = snapshot;
            editor?.setContent(snapshot);
            this.checkDirty();
            this.updatedAt = Date.now();
        },

        redo() {
            const editor = getEditor();
            if (editor?.canRedo()) {
                editor.redo();
                return;
            }
            if (this.historyPointer >= this.contentHistory.length) return;

            this.historyPointer++;
            const snapshot = this.historyPointer === this.contentHistory.length
                ? this._snapshotHead
                : this.contentHistory[this.historyPointer];
            if (snapshot === undefined) return;
            this.content = snapshot;
            editor?.setContent(snapshot);
            this.checkDirty();
            this.updatedAt = Date.now();
        },
    };
}
