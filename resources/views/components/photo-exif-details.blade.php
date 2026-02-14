@props(['photo'])

<dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-2">
    @if($photo->meta['camera_model'] ?? null)
        <div>
            <dt class="font-semibold text-base-content/70">Camera:</dt>
            <dd class="text-base-content">{{ $photo->meta['camera_model'] }}</dd>
        </div>
    @endif

    @if($photo->meta['lens'] ?? null)
        <div>
            <dt class="font-semibold text-base-content/70">Lens:</dt>
            <dd class="text-base-content">{{ $photo->meta['lens'] }}</dd>
        </div>
    @endif

    @if($photo->meta['iso'] ?? null)
        <div>
            <dt class="font-semibold text-base-content/70">ISO:</dt>
            <dd class="text-base-content">{{ $photo->meta['iso'] }}</dd>
        </div>
    @endif

    @if($photo->meta['aperture'] ?? null)
        <div>
            <dt class="font-semibold text-base-content/70">Aperture:</dt>
            <dd class="text-base-content">f/{{ $photo->meta['aperture'] }}</dd>
        </div>
    @endif

    @if($photo->meta['shutter_speed'] ?? null)
        <div>
            <dt class="font-semibold text-base-content/70">Shutter Speed:</dt>
            <dd class="text-base-content">{{ $photo->meta['shutter_speed'] }}s</dd>
        </div>
    @endif

    @if($photo->meta['focal_length'] ?? null)
        <div>
            <dt class="font-semibold text-base-content/70">Focal Length:</dt>
            <dd class="text-base-content">{{ $photo->meta['focal_length'] }}mm</dd>
        </div>
    @endif

    @if(($photo->meta['width'] ?? null) && ($photo->meta['height'] ?? null))
        <div>
            <dt class="font-semibold text-base-content/70">Dimensions:</dt>
            <dd class="text-base-content">{{ $photo->meta['width'] }} × {{ $photo->meta['height'] }} px</dd>
        </div>
    @endif

    @if($photo->meta['created_at'] ?? null)
        <div>
            <dt class="font-semibold text-base-content/70">Date Taken (EXIF):</dt>
            <dd class="text-base-content">{{ $photo->meta['created_at'] }}</dd>
        </div>
    @endif
</dl>
