<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

final class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = setting('robots_txt', "User-agent: *\nDisallow:\n");

        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}
