<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function show(Request $request, Media $media, string $conversion = ''): StreamedResponse
    {
        if ($media->disk !== 'private') {
            abort(404);
        }

        return $media->toInlineResponse($request, $conversion);
    }
}
