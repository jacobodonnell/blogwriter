<x-layouts.admin>
    @section('title', 'Settings')

    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold">Settings</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure your BlogWriter site.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Site Settings --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Site Information</h2>

                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Site Name</span>
                            </label>
                            <input type="text" class="input input-bordered" value="{{ config('app.name') }}" disabled />
                            <label class="label">
                                <span class="label-text-alt">Edit in your <code>.env</code> file as <code>APP_NAME</code></span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Site URL</span>
                            </label>
                            <input type="text" class="input input-bordered" value="{{ config('app.url') }}" disabled />
                            <label class="label">
                                <span class="label-text-alt">Edit in your <code>.env</code> file as <code>APP_URL</code></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User Profile --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Your Profile</h2>

                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-primary text-primary-content flex items-center justify-center text-2xl font-bold">
                                {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-lg">{{ auth()->user()?->name ?? 'User' }}</p>
                                <p class="text-sm text-gray-500">{{ auth()->user()?->email ?? '' }}</p>
                            </div>
                        </div>

                        <div class="divider my-2"></div>

                        <div class="alert alert-info">
                            <i class="ph ph-info text-xl"></i>
                            <span>Profile editing coming soon. For now, use the CLI command <code>blogwriter:user:create</code> to manage users.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Environment --}}
            <div class="card bg-base-100 shadow lg:col-span-2">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Environment</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Environment</div>
                            <div class="stat-value text-lg">{{ config('app.env') }}</div>
                        </div>

                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Debug Mode</div>
                            <div class="stat-value text-lg @if(config('app.debug')) text-success @else text-error @endif">
                                {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                            </div>
                        </div>

                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Version</div>
                            <div class="stat-value text-lg">v0.1a Alpha</div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-base-200 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <i class="ph ph-terminal text-lg mr-2"></i>
                            Use <code class="bg-base-300 px-2 py-1 rounded">php artisan blogwriter:install</code> to reconfigure your site,
                            or edit the <code class="bg-base-300 px-2 py-1 rounded">.env</code> file directly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
