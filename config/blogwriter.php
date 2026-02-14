<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Installer
    |--------------------------------------------------------------------------
    |
    | Enable the browser-based installer at /install. This is disabled by
    | default because it requires a setup script that hasn't been written
    | yet. Use the CLI installer (php artisan blogwriter:install) instead.
    |
    */

    'web_installer' => env('BLOGWRITER_WEB_INSTALLER', false),

];
