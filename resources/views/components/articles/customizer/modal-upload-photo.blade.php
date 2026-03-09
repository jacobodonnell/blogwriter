<x-editor-modal x-ref="uploadPhotoModal" title="Upload Featured Image" maxWidth="max-w-xl">
    <div x-data="uploadPhotoModal()">
        <div class="space-y-3">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Image</legend>
                <input type="file" id="photo-file-picker" x-ref="filePicker" data-test="photo-file-picker"
                       class="file-input file-input-bordered w-full"
                       accept="image/jpeg,image/jpg,image/png,image/webp"
                       data-max-size-kb="{{ config('app.max_image_upload_kb') }}"
                       @change="handleFileChange($event)">
                <img x-show="uploadPreview" :src="uploadPreview"
                     class="w-full max-h-40 object-contain rounded-lg mt-2"
                     alt="Upload preview"
                     x-cloak>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Alt Text</legend>
                <input type="text" id="photo-alt-text" x-ref="altInput" data-test="photo-alt-text"
                       class="input input-bordered w-full"
                       placeholder="Describe the image for accessibility">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Caption (optional)</legend>
                <textarea id="photo-caption" x-ref="captionInput"
                          class="textarea textarea-bordered w-full h-16 text-sm"
                          placeholder="Photo caption"></textarea>
            </fieldset>

            <div class="alert alert-warning text-sm mt-3">
                <i class="ph ph-warning"></i>
                <span>This photo will be published when you save the article.</span>
            </div>
        </div>

        {{-- Action buttons rendered inline (inside uploadPhotoModal scope for $refs access) --}}
        <div class="modal-action">
            <button type="button" class="btn btn-primary" data-test="attach-photo"
                    @click="
                        if (!$refs.filePicker.files[0] || !$refs.altInput.value.trim()) return;
                        $dispatch('photo-attached', {
                            file: $refs.filePicker.files[0],
                            alt: $refs.altInput.value,
                            caption: $refs.captionInput.value
                        });
                        $el.closest('dialog').close();
                    ">
                Attach Photo
            </button>
            <form method="dialog">
                <button class="btn">Cancel</button>
            </form>
        </div>
    </div>
</x-editor-modal>
