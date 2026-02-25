<x-articles.customizer
    :article="$article"
    :categories="$categories"
    :photos="$photos"
    :is-new="$isNew ?? false"
    :live-content="$liveContent ?? ''"
    :live-title="$liveTitle ?? ''"
    :live-slug="$liveSlug ?? ''"
    :live-summary="$liveSummary ?? ''"
    :live-category-id="$liveCategoryId ?? null"
    :live-photo-id="$livePhotoId ?? null"
    :live-external-featured-img-url="$liveExternalFeaturedImgUrl ?? null"
    :live-meta="$liveMeta ?? []"
/>
