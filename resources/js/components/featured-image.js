export default function featuredImage(config) {
    return {
        photoUrls: config.photoUrls,
        photoCaptions: config.photoCaptions,

        get previewUrl() {
            if (this.uploadedPhotoUrl) return this.uploadedPhotoUrl;
            if (this.selectedPhotoId && this.photoUrls[this.selectedPhotoId]) return this.photoUrls[this.selectedPhotoId];
            return null;
        },
    };
}
