export default function featuredImage(config) {
    return {
        photoUrls: config.photoUrls,
        photoCaptions: config.photoCaptions,

        get previewUrl() {
            if (this.uploadedPhotoUrl) return this.uploadedPhotoUrl;
            if (this.selectedPhotoId && this.photoUrls[this.selectedPhotoId]) return this.photoUrls[this.selectedPhotoId];
            return null;
        },

        clearFileInput() {
            const fi = this.$refs.featuredImageFileInput;
            if (fi) fi.value = '';
        },

        selectPhoto() {
            this.hasNewPhoto = false;
            this.uploadedPhotoUrl = null;
            this.clearFileInput();
            if (!this.selectedPhotoId) return;
            this.featuredImageUrl = '';
            this.usePhotoCaption = false;
            this.featuredImageCaption = '';
        },

        setExternalUrl() {
            this.hasNewPhoto = false;
            this.uploadedPhotoUrl = null;
            this.clearFileInput();
            if (!this.featuredImageUrl) return;
            this.selectedPhotoId = '';
            this.usePhotoCaption = false;
        },
    };
}
