import { createTiptapEditor } from '../extensions/tiptap-editor';

export default function articleCustomizer(config) {
    let editor = null;

    return {
        title: config.title,
        slug: config.slug,
        content: config.content,
        summary: config.summary,
        selectedPhotoId: config.selectedPhotoId,
        uploadedPhotoUrl: null,
        uploading: false,
        featuredImageUrl: config.featuredImageUrl,
        showUrlField: config.showUrlField,
        featuredImageCaption: config.featuredImageCaption,
        usePhotoCaption: config.usePhotoCaption,
        showLinkDialog: false,
        linkUrl: '',
        showImageDialog: false,
        editingImage: false,
        imageUrl: '',
        imageAlt: '',
        imageCaption: '',
        showYoutubeDialog: false,
        youtubeUrl: '',
        initialStatus: config.initialStatus,
        currentStatus: config.currentStatus,
        wasEverPublished: config.wasEverPublished,
        originalPublishedAt: config.originalPublishedAt,
        contentError: false,
        editorReady: false,
        hasNewPhoto: false,
        updatedAt: 0,
        hasChanges: config.hasDraft ?? false,
        savedContent: config.committedContent,
        isNew: config.isNew ?? false,

        get isPlaceholderSlug() {
            return /^untitled-[a-z0-9]{8}$/.test(this.slug);
        },

        get displaySlug() {
            return this.isPlaceholderSlug ? '' : this.slug;
        },
        set displaySlug(v) {
            this.slug = v;
        },

        generateSlug() {
            if (this.title && (!this.slug || this.slug.match(/^untitled-[a-z0-9]{8}$/))) {
                this.slug = this.title.toLowerCase()
                    .replace(/[''`\u2018\u2019\u2032\u02BC]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        },

        get buttonAction() {
            if (this.currentStatus === 'published' && this.initialStatus === 'draft' && !this.wasEverPublished) return 'publish';
            if (this.currentStatus === 'published' && this.initialStatus === 'draft' && this.wasEverPublished) return 'republish';
            if (this.currentStatus === 'draft' && this.initialStatus === 'published') return 'unpublish';
            return 'save';
        },
        get buttonLabel() {
            const a = this.buttonAction;
            const p = this.hasNewPhoto;
            if (a === 'publish') return p ? 'Upload Photo & Publish' : 'Publish Article';
            if (a === 'republish') return p ? 'Upload Photo & Republish' : 'Republish Article';
            if (a === 'unpublish') return 'Unpublish Article';
            if (this.isNew) return 'Save to keep';
            if (p) return this.initialStatus === 'published' ? 'Upload Photo & Publish' : 'Upload Photo & Save Draft';
            if (this.hasChanges) return this.initialStatus === 'published' ? 'Publish Updates' : 'Save Draft';
            return this.initialStatus === 'published' ? 'Published' : 'Saved';
        },
        get buttonIcon() {
            const a = this.buttonAction;
            if (a === 'publish' || a === 'republish') return 'ph-rocket-launch';
            if (a === 'unpublish') return 'ph-arrow-u-up-left';
            if (this.isNew) return 'ph-warning';
            if (this.hasNewPhoto) return 'ph-upload-simple';
            if (this.hasChanges) return this.initialStatus === 'published' ? 'ph-cloud-arrow-up' : 'ph-floppy-disk';
            return 'ph-check';
        },
        get buttonClass() {
            const a = this.buttonAction;
            if (a === 'publish' || a === 'republish') return 'btn-success';
            if (a === 'unpublish') return 'btn-error btn-outline';
            if (this.isNew) return 'btn-warning';
            if (this.hasNewPhoto) return 'btn-success';
            if (!this.hasChanges) return 'btn-ghost opacity-60';
            return 'btn-primary';
        },

        get wordCount() {
            void this.updatedAt;
            return editor?.wordCount ?? 0;
        },

        init() {
            this.$nextTick(() => {
                const el = this.$refs.contentEditor;
                if (!el) return;

                editor = createTiptapEditor({
                    element: el,
                    content: this.content,
                    onUpdate: (e) => {
                        this.updatedAt = Date.now();
                        this.content = e.getMarkdown();
                        this.hasChanges = this.content.trim() !== this.savedContent.trim();
                        this.contentError = false;
                        this.$refs.customizerForm.dispatchEvent(
                            new Event('input', { bubbles: true })
                        );
                    },
                    onSelectionUpdate: () => {
                        this.updatedAt = Date.now();
                    },
                });

                this.editorReady = true;
                this.updatedAt = Date.now();
            });

            const store = Alpine.store('saveButton');
            store.label = this.buttonLabel;
            store.icon = this.buttonIcon;
            store.cssClass = this.buttonClass;
            store.action = this.buttonAction;
            store.ready = true;

            this.$watch('buttonLabel', v => store.label = v);
            this.$watch('buttonIcon', v => store.icon = v);
            this.$watch('buttonClass', v => store.cssClass = v);
            this.$watch('buttonAction', v => store.action = v);

            this._onSaveArticle = () => {
                if (this.buttonAction === 'publish') { this.$refs.publishModal.showModal(); return; }
                if (this.buttonAction === 'republish') { this.$refs.republishModal.showModal(); return; }
                if (this.buttonAction === 'unpublish') { this.$refs.unpublishModal.showModal(); return; }
                this.submitFullSave();
            };

            this._onKeydown = (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 's') {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('save-article'));
                }
            };

            window.addEventListener('save-article', this._onSaveArticle);
            window.addEventListener('keydown', this._onKeydown);
        },

        command(name) {
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
            return editor?.isActive(name, attrs) ?? false;
        },

        insertLink() {
            if (!this.linkUrl) return;
            editor.setLink({ href: this.linkUrl });
            this.showLinkDialog = false;
        },

        openEditImage() {
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
            return editor?.getAttributes('image')?.width == null;
        },

        resetImageDialog() {
            this.imageUrl     = '';
            this.imageAlt     = '';
            this.imageCaption = '';
            this.editingImage = false;
        },

        insertYoutube() {
            if (!this.youtubeUrl) return;
            editor.setYoutubeVideo({ src: this.youtubeUrl });
            this.showYoutubeDialog = false;
        },

        handlePhotoAttached({ file, alt, caption }) {
            if (!file || !alt.trim()) return;

            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.featuredImageFileInput.files = dt.files;
            this.$refs.featuredImageAltInput.value = alt.trim();
            this.$refs.featuredImageCaptionInput.value = caption;

            this.uploadedPhotoUrl = URL.createObjectURL(file);
            this.selectedPhotoId = '';
            this.featuredImageUrl = '';
            this.showUrlField = false;
            this.hasNewPhoto = true;
        },

        submitFullSave() {
            if (editor) {
                this.content = editor.getMarkdown();
            }
            if (!this.content?.trim()) {
                this.contentError = true;
                editor?.focus();
                return;
            }
            const form = this.$refs.customizerForm;
            form.removeAttribute('x-target');
            form.action = config.saveRoute;
            form.submit();
        },

        destroy() {
            window.removeEventListener('save-article', this._onSaveArticle);
            window.removeEventListener('keydown', this._onKeydown);
            editor?.destroy();
            editor = null;
        },
    };
}
