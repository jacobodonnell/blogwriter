export default function articleCustomizer(config) {
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
        easyMDE: null,
        initialStatus: config.initialStatus,
        currentStatus: config.currentStatus,
        wasEverPublished: config.wasEverPublished,
        originalPublishedAt: config.originalPublishedAt,
        contentError: false,
        hasNewPhoto: false,

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
            if (this.initialStatus === 'published') return p ? 'Upload Photo & Save Changes' : 'Save Changes';
            return p ? 'Upload Photo & Save Draft' : 'Save Draft';
        },
        get buttonIcon() {
            if (this.hasNewPhoto) return 'ph-upload-simple';
            if (this.buttonAction === 'publish' || this.buttonAction === 'republish') return 'ph-rocket-launch';
            if (this.buttonAction === 'unpublish') return 'ph-arrow-u-up-left';
            return 'ph-floppy-disk';
        },
        get buttonClass() {
            if (this.hasNewPhoto) return 'btn-success';
            if (this.buttonAction === 'publish' || this.buttonAction === 'republish') return 'btn-success';
            if (this.buttonAction === 'unpublish') return 'btn-error btn-outline';
            return 'btn-primary';
        },

        init() {
            this.$nextTick(() => {
                const ta = document.getElementById('content-editor');
                if (!ta) return;

                this.easyMDE = new EasyMDE({
                    element: ta,
                    forceSync: true,
                    spellChecker: false,
                    status: false,
                    placeholder: '## Write your article here...',
                    toolbar: [
                        'bold', 'italic', 'heading-2', 'heading-3', '|',
                        'quote', 'unordered-list', 'ordered-list', '|',
                        'link', 'image', 'code', 'horizontal-rule', '|',
                        'guide'
                    ],
                    initialValue: this.content,
                });

                this.easyMDE.codemirror.on('change', () => {
                    this.content = this.easyMDE.value();
                    this.contentError = false;
                    document.getElementById('customizer-form').dispatchEvent(
                        new Event('input', { bubbles: true })
                    );
                });
            });
        },

        attachPhoto() {
            const picker = document.getElementById('photo-file-picker');
            const altInput = document.getElementById('photo-alt-text-input');
            if (!picker.files[0] || !altInput.value.trim()) return;

            const dt = new DataTransfer();
            dt.items.add(picker.files[0]);
            document.getElementById('featured-image-file-input').files = dt.files;
            document.getElementById('featured-image-alt-input').value = altInput.value.trim();
            document.getElementById('featured-image-caption-input').value = document.getElementById('photo-caption-input').value;

            this.uploadedPhotoUrl = URL.createObjectURL(picker.files[0]);
            this.selectedPhotoId = '';
            this.featuredImageUrl = '';
            this.showUrlField = false;
            this.hasNewPhoto = true;

            document.getElementById('upload-photo-modal').close();
        },

        submitFullSave() {
            if (this.easyMDE) {
                this.content = this.easyMDE.value();
            }
            if (!this.content || !this.content.trim()) {
                this.contentError = true;
                if (this.easyMDE) {
                    this.easyMDE.codemirror.focus();
                }
                return;
            }
            let form = document.getElementById('customizer-form');
            form.removeAttribute('x-target');
            form.action = config.saveRoute;
            form.submit();
        },
    };
}
