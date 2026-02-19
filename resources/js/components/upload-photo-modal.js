export default function uploadPhotoModal() {
    return {
        uploadPreview: null,

        handleFileChange(event) {
            if (event.target.files[0]) {
                this.uploadPreview = URL.createObjectURL(event.target.files[0]);
            }
        },
    };
}
