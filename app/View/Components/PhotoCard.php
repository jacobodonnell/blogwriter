<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Enums\Status;
use App\Models\Photo;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class PhotoCard extends Component
{
    public string $photoUrl;

    public string $imageUrl;

    public bool $isDraft;

    public function __construct(
        public Photo $photo,
        public string $authorName = '',
        public bool $showAuthOverlays = true,
    ) {
        $this->photoUrl = route('photos.show', $photo->slug);
        $this->imageUrl = $photo->thumbnail_url ?? $photo->image_url ?? '';
        $this->isDraft = $photo->status === Status::Private;
    }

    public function render(): View
    {
        return view('components.photo-card');
    }
}
