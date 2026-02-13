<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install BlogWriter</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-100 flex items-center justify-center">
    <div class="max-w-2xl w-full mx-4">
        <div class="card bg-base-200 shadow-xl">
            <div class="card-body">
                <div class="font-mono">
                    <div class="text-success mb-4">
                        ┌────────────────────────────────────────────────────────┐
                        │                  BlogWriter Installer                  │
                        │              Own Your Content. Own Your Domain.        │
                        └────────────────────────────────────────────────────────┘
                    </div>

                    <div class="text-base-content mb-6">
                        <p class="mb-4">BlogWriter is not installed yet.</p>

                        <div class="alert alert-info mb-4">
                            <span class="font-bold">To install BlogWriter, run this command in your terminal:</span>
                        </div>

                        <div class="bg-base-300 p-4 rounded-lg mb-4 font-mono text-sm">
                            php artisan blogwriter:install
                        </div>

                        <p class="mb-4">You'll need SSH or terminal access to your server to run this command.</p>

                        <p>If you don't have terminal access, you may need to:</p>
                        <ul class="list-disc list-inside mb-4 ml-4">
                            <li>Use your hosting provider's SSH feature</li>
                            <li>Contact your hosting support for assistance</li>
                            <li>Install locally and then deploy the configured files</li>
                        </ul>
                    </div>

                    <div class="divider"></div>

                    <div class="text-sm text-base-content/70">
                        <p>Need help? Check the <a href="https://blogwriter.tech/docs/getting-started/installation" class="link link-primary" target="_blank">installation documentation</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
