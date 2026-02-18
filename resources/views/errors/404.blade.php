<x-layouts.public title="Page Not Found">
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <h1 class="text-6xl font-bold text-base-content/20 mb-4">404</h1>
        <h2 class="text-2xl font-semibold mb-2">Page Not Found</h2>
        <p class="text-base-content/60 mb-8 max-w-md">
            The page you're looking for doesn't exist or has been moved.
        </p>
        <a href="{{ route('home') }}" class="btn btn-primary">
            <i class="ph ph-house"></i>
            Go Home
        </a>
    </div>
</x-layouts.public>
