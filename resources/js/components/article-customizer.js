import { createTiptapEditor } from '../extensions/tiptap-editor';

export default function articleCustomizer(config) {
    let editor = null;

    return {
        title: config.title,
        slug: config.slug,
        content: config.content,
        summary: config.summary,
        categoryId: String(config.categoryId ?? ''),
        metaTitle: config.metaTitle ?? '',
        metaDescription: config.metaDescription ?? '',
        ogImage: config.ogImage ?? '',
        selectedPhotoId: config.selectedPhotoId,
        uploadedPhotoUrl: null,
        uploading: false,
        featuredImageUrl: config.featuredImageUrl,
        showUrlField: config.showUrlField,
        featuredImageCaption: config.featuredImageCaption,
        usePhotoCaption: config.usePhotoCaption,
        editorHeight: this.$persist('32rem').as('editorHeight'),
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
        markdownMode: false,
        hasNewPhoto: false,
        hasNewCategory: false,
        newCategoryName: '',
        updatedAt: 0,
        hasDraft: config.hasDraft ?? false,
        hasChanges: config.hasDraft ?? false,
        isNew: config.isNew ?? false,
        savedContent: config.savedContent ?? '',
        savedTitle: config.savedTitle ?? '',
        savedSlug: config.savedSlug ?? '',
        savedSummary: config.savedSummary ?? '',
        savedCategoryId: String(config.savedCategoryId ?? ''),
        savedPhotoId: String(config.savedPhotoId ?? ''),
        savedFeaturedImageUrl: config.savedFeaturedImageUrl ?? '',
        savedFeaturedImageCaption: config.savedFeaturedImageCaption ?? '',
        savedUsePhotoCaption: config.savedUsePhotoCaption ?? false,
        savedMetaTitle: config.savedMetaTitle ?? '',
        savedMetaDescription: config.savedMetaDescription ?? '',
        savedOgImage: config.savedOgImage ?? '',

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

        isClean() {
            return this.content                          === this.savedContent
                && this.title                            === this.savedTitle
                && this.slug                             === this.savedSlug
                && this.summary                          === this.savedSummary
                && String(this.categoryId || '')          === this.savedCategoryId
                && String(this.selectedPhotoId || '')     === this.savedPhotoId
                && (this.featuredImageUrl || '')          === this.savedFeaturedImageUrl
                && (this.featuredImageCaption || '')      === this.savedFeaturedImageCaption
                && this.usePhotoCaption                  === this.savedUsePhotoCaption
                && (this.metaTitle || '')                 === this.savedMetaTitle
                && (this.metaDescription || '')           === this.savedMetaDescription
                && (this.ogImage || '')                   === this.savedOgImage;
        },

        checkDirty() {
            const dirty = !this.isClean();
            this.hasChanges = dirty;
            // Only clear hasDraft when there is no pre-existing persisted draft.
            // If the DB already has a draft snapshot, the toggle stays visible even
            // if the user edits back to the live values (those saved fields differ).
            if (dirty || !config.hasDraft) {
                this.hasDraft = dirty;
            }
            if (dirty && Alpine.store('preview').mode === 'live') {
                Alpine.store('preview').mode = 'draft';
            }
        },

        isDraftField(field) {
            const map = {
                title:                [this.title, this.savedTitle],
                slug:                 [this.slug, this.savedSlug],
                content:              [this.content, this.savedContent],
                summary:              [this.summary, this.savedSummary],
                categoryId:           [String(this.categoryId || ''), this.savedCategoryId],
                featuredImage:        [
                    JSON.stringify({ photoId: String(this.selectedPhotoId || ''), url: this.featuredImageUrl || '', caption: this.featuredImageCaption || '', useCaption: this.usePhotoCaption }),
                    JSON.stringify({ photoId: String(this.savedPhotoId || ''), url: this.savedFeaturedImageUrl || '', caption: this.savedFeaturedImageCaption || '', useCaption: this.savedUsePhotoCaption }),
                ],
                metaTitle:            [(this.metaTitle || ''), this.savedMetaTitle],
                metaDescription:      [(this.metaDescription || ''), this.savedMetaDescription],
                ogImage:              [(this.ogImage || ''), this.savedOgImage],
            };
            const pair = map[field];
            if (!pair) return false;
            return pair[0] !== pair[1];
        },

        revertField(field) {
            const revertMap = {
                title:           () => { this.title = this.savedTitle; },
                slug:            () => { this.slug = this.savedSlug; },
                content:         () => {
                    this.content = this.savedContent;
                    if (editor) editor.setContent(this.savedContent);
                },
                summary:         () => { this.summary = this.savedSummary; },
                categoryId:      () => {
                    this.categoryId = this.savedCategoryId;
                    this.hasNewCategory = false;
                    this.newCategoryName = '';
                },
                metaTitle:       () => { this.metaTitle = this.savedMetaTitle; },
                metaDescription: () => { this.metaDescription = this.savedMetaDescription; },
                ogImage:         () => { this.ogImage = this.savedOgImage; },
            };
            const fn = revertMap[field];
            if (fn) {
                fn();
                this.checkDirty();
                this.$refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },

        revertFeaturedImage() {
            // Restore common saved values
            this.featuredImageCaption = this.savedFeaturedImageCaption;
            this.usePhotoCaption = this.savedUsePhotoCaption;
            this.uploadedPhotoUrl = null;
            this.hasNewPhoto = false;

            // Clear the staged file input DOM element
            const fi = this.$refs.featuredImageFileInput;
            if (fi) fi.value = '';

            // Enforce mutual exclusion based on saved state
            if (this.savedFeaturedImageUrl) {
                // Reverting to external URL mode
                this.selectedPhotoId = '';
                this.featuredImageUrl = this.savedFeaturedImageUrl;
                this.showUrlField = true;
            } else if (this.savedPhotoId) {
                // Reverting to photo mode
                this.selectedPhotoId = this.savedPhotoId;
                this.featuredImageUrl = '';
                this.showUrlField = false;
            } else {
                // Reverting to no image
                this.selectedPhotoId = '';
                this.featuredImageUrl = '';
                this.showUrlField = false;
            }

            this.checkDirty();
            this.$refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }));
        },

        get stagedItemPrefix() {
            if (this.hasNewPhoto) return 'Upload Photo';
            if (this.hasNewCategory) return 'Create Category';
            return '';
        },

        get buttonAction() {
            if (this.currentStatus === 'published' && this.initialStatus === 'draft' && !this.wasEverPublished) return 'publish';
            if (this.currentStatus === 'published' && this.initialStatus === 'draft' && this.wasEverPublished) return 'republish';
            if (this.currentStatus === 'draft' && this.initialStatus === 'published') return 'unpublish';
            return 'save';
        },
        get buttonLabel() {
            const action = this.buttonAction;
            const suffix = this.stagedItemPrefix;

            if (action === 'publish') return suffix ? `${suffix} & Publish` : 'Publish Article';
            if (action === 'republish') return suffix ? `${suffix} & Republish` : 'Republish Article';
            if (action === 'unpublish') return 'Unpublish Article';
            if (this.isNew) return 'Save Draft';

            const isPublished = this.initialStatus === 'published';

            if (suffix) return isPublished ? `${suffix} & Publish` : `${suffix} & Save Draft`;
            if (this.hasChanges) return isPublished ? 'Publish Updates' : 'Save Draft';
            return isPublished ? 'Published' : 'Saved';
        },
        get buttonIcon() {
            const a = this.buttonAction;
            if (a === 'publish' || a === 'republish') return 'ph-rocket-launch';
            if (a === 'unpublish') return 'ph-arrow-u-up-left';
            if (this.isNew) return 'ph-floppy-disk';
            if (this.hasNewPhoto) return 'ph-upload-simple';
            if (this.hasNewCategory) return 'ph-tag';
            if (this.hasChanges) return this.initialStatus === 'published' ? 'ph-cloud-arrow-up' : 'ph-floppy-disk';
            return 'ph-check';
        },
        get buttonClass() {
            const a = this.buttonAction;
            if (a === 'publish' || a === 'republish') return 'btn-success';
            if (a === 'unpublish') return 'btn-error btn-outline';
            if (this.isNew) return 'btn-warning';
            if (this.hasNewPhoto || this.hasNewCategory) return 'btn-success';
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
                        this.checkDirty();
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
                const normalised = editor.getMarkdown();
                this.savedContent = normalised; // capture normalised baseline after parse round-trip
                this.content = normalised;      // sync so isClean() starts true
                this.updatedAt = Date.now();

                const ro = new ResizeObserver(entries => {
                    for (const entry of entries) {
                        if (entry.target.style.height) {
                            this.editorHeight = entry.target.style.height;
                        }
                    }
                });
                ro.observe(el);
            });

            const store = Alpine.store('saveButton');
            store.label = this.buttonLabel;
            store.icon = this.buttonIcon;
            store.cssClass = this.buttonClass;
            store.action = this.buttonAction;
            store.ready = true;
            store.hasDraft = this.hasDraft;

            this.$watch('buttonLabel', v => store.label = v);
            this.$watch('buttonIcon', v => store.icon = v);
            this.$watch('buttonClass', v => store.cssClass = v);
            this.$watch('buttonAction', v => store.action = v);
            this.$watch('hasDraft', v => store.hasDraft = v);

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

            this._onBeforeUnload = (e) => {
                if (this.isNew && this.hasChanges) { e.preventDefault(); }
            };

            window.addEventListener('save-article', this._onSaveArticle);
            window.addEventListener('keydown', this._onKeydown);
            window.addEventListener('beforeunload', this._onBeforeUnload);
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
            this.hasChanges = false;
            const form = this.$refs.customizerForm;
            form.removeAttribute('x-target');
            form.action = config.saveRoute;
            form.submit();
        },

        toggleMarkdownMode() {
            if (!this.markdownMode) {
                this.content = editor.getMarkdown();
            } else {
                editor.setContent(this.content);
            }
            this.markdownMode = !this.markdownMode;
            this.checkDirty();
        },

        destroy() {
            window.removeEventListener('save-article', this._onSaveArticle);
            window.removeEventListener('keydown', this._onKeydown);
            window.removeEventListener('beforeunload', this._onBeforeUnload);
            editor?.destroy();
            editor = null;
        },
    };
}
