export function makeDirtyTracker(config, getEditor) {
    return {
        hasDraft: config.hasDraft ?? false,
        hasChanges: config.hasDraft ?? false,
        savedContent: config.savedContent ?? '',
        savedTitle: config.savedTitle ?? '',
        savedSlug: config.savedSlug ?? '',
        savedSummary: config.savedSummary ?? '',
        savedCategoryId: String(config.savedCategoryId ?? ''),
        savedPhotoId: String(config.savedPhotoId ?? ''),
        savedFeaturedImageUrl: config.savedFeaturedImageUrl ?? '',
        savedFeaturedImageAlt: config.savedFeaturedImageAlt ?? '',
        savedFeaturedImageCaption: config.savedFeaturedImageCaption ?? '',
        savedUsePhotoCaption: config.savedUsePhotoCaption ?? false,
        savedMetaTitle: config.savedMetaTitle ?? '',
        savedMetaDescription: config.savedMetaDescription ?? '',
        savedOgImage: config.savedOgImage ?? '',

        isClean() {
            return this.content                          === this.savedContent
                && this.title                            === this.savedTitle
                && this.slug                             === this.savedSlug
                && this.summary                          === this.savedSummary
                && String(this.categoryId || '')          === this.savedCategoryId
                && String(this.selectedPhotoId || '')     === this.savedPhotoId
                && (this.featuredImageUrl || '')          === this.savedFeaturedImageUrl
                && (this.featuredImageAlt || '')           === this.savedFeaturedImageAlt
                && (this.featuredImageCaption || '')      === this.savedFeaturedImageCaption
                && this.usePhotoCaption                  === this.savedUsePhotoCaption
                && (this.metaTitle || '')                 === this.savedMetaTitle
                && (this.metaDescription || '')           === this.savedMetaDescription
                && (this.ogImage || '')                   === this.savedOgImage;
        },

        checkDirty() {
            const dirty = !this.isClean();
            this.hasChanges = dirty;
            if (dirty || !config.hasDraft) {
                this.hasDraft = dirty;
            }
            if (dirty && Alpine.store('preview').mode === 'live') {
                Alpine.store('preview').mode = 'draft';
            }
        },

        isDraftField(field) {
            if (field === 'featuredImage') {
                return this._featuredImageSnapshot() !== this._savedFeaturedImageSnapshot();
            }

            const map = {
                title:           [this.title, this.savedTitle],
                slug:            [this.slug, this.savedSlug],
                content:         [this.content, this.savedContent],
                summary:         [this.summary, this.savedSummary],
                categoryId:      [String(this.categoryId || ''), this.savedCategoryId],
                metaTitle:       [(this.metaTitle || ''), this.savedMetaTitle],
                metaDescription: [(this.metaDescription || ''), this.savedMetaDescription],
                ogImage:         [(this.ogImage || ''), this.savedOgImage],
            };
            const pair = map[field];
            if (!pair) return false;
            return pair[0] !== pair[1];
        },

        _featuredImageSnapshot() {
            return JSON.stringify({
                photoId: String(this.selectedPhotoId || ''),
                url: this.featuredImageUrl || '',
                alt: this.featuredImageAlt || '',
                caption: this.featuredImageCaption || '',
                useCaption: this.usePhotoCaption,
            });
        },

        _savedFeaturedImageSnapshot() {
            return JSON.stringify({
                photoId: String(this.savedPhotoId || ''),
                url: this.savedFeaturedImageUrl || '',
                alt: this.savedFeaturedImageAlt || '',
                caption: this.savedFeaturedImageCaption || '',
                useCaption: this.savedUsePhotoCaption,
            });
        },

        revertField(field) {
            const editor = getEditor();
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
            this.featuredImageAlt = this.savedFeaturedImageAlt;
            this.featuredImageCaption = this.savedFeaturedImageCaption;
            this.usePhotoCaption = this.savedUsePhotoCaption;
            this.uploadedPhotoUrl = null;
            this.hasNewPhoto = false;

            const fi = this.$refs.featuredImageFileInput;
            if (fi) fi.value = '';

            if (this.savedFeaturedImageUrl) {
                this.selectedPhotoId = '';
                this.featuredImageUrl = this.savedFeaturedImageUrl;
                this.showUrlField = true;
            } else if (this.savedPhotoId) {
                this.selectedPhotoId = this.savedPhotoId;
                this.featuredImageUrl = '';
                this.showUrlField = false;
            } else {
                this.selectedPhotoId = '';
                this.featuredImageUrl = '';
                this.showUrlField = false;
            }

            this.checkDirty();
            this.$refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }));
        },
    };
}
