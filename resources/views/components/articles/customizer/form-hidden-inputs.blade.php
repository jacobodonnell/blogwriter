@props(['isNew'])

@csrf
@unless($isNew)
    @method('PUT')
@endunless

{{-- Hidden file inputs for staged photo upload --}}
<input type="file" x-ref="featuredImageFileInput" name="featured_image_file" class="hidden">
<input type="hidden" x-ref="featuredImageAltInput" name="featured_image_alt" value="">
<input type="hidden" x-ref="featuredImageCaptionInput" name="featured_image_caption" value="">

{{-- Hidden inputs for staged new category --}}
<input type="hidden" name="new_category_name" x-ref="newCategoryNameInput">
<input type="hidden" name="new_category_slug" x-ref="newCategorySlugInput">
<input type="hidden" name="new_category_parent_id" x-ref="newCategoryParentIdInput">
<input type="hidden" name="new_category_description" x-ref="newCategoryDescriptionInput">
<input type="hidden" name="meta[featured_image_caption]" :value="usePhotoCaption ? '' : featuredImageCaption">
<input type="hidden" name="meta[use_photo_caption]" :value="usePhotoCaption ? '1' : ''">
<input type="hidden" name="remove_featured_image" x-ref="removeFeaturedImageInput" value="0">
<input type="hidden" name="history_pointer" :value="historyPointer">
