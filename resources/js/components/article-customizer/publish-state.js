export function makePublishState() {
    return {
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
    };
}
