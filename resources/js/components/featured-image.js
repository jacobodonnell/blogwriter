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
            const fi = document.getElementById('featured-image-file-input');
            if (fi) fi.value = '';
        },

        selectPhoto() {
            if (!this.selectedPhotoId) return;
            this.featuredImageUrl = '';
            this.uploadedPhotoUrl = null;
            this.hasNewPhoto = false;
            this.usePhotoCaption = false;
            this.featuredImageCaption = '';
            this.clearFileInput();
        },

        setExternalUrl() {
            if (!this.featuredImageUrl) return;
            this.selectedPhotoId = '';
            this.uploadedPhotoUrl = null;
            this.hasNewPhoto = false;
            this.usePhotoCaption = false;
            this.clearFileInput();
        },
    };
}
