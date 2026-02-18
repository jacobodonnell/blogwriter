<x-settings-layout active="site">
    <div class="space-y-6">
        {{-- Default Placeholder Image --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-xl mb-4">Default Image</h2>
                <p class="text-sm text-base-content/60 mb-4">Used as a fallback when articles have no featured image.</p>

                @php $placeholderUrl = placeholder_image_url(); @endphp
                @if($placeholderUrl)
                    <div class="mb-4">
                        <img src="{{ $placeholderUrl }}" alt="Current placeholder" class="w-full max-w-xs rounded-lg shadow" />
                    </div>
                @else
                    <div class="mb-4 text-base-content/40 text-sm">No placeholder image set.</div>
                @endif

                <form action="{{ route('admin.settings.site.image.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-control">
                        <input type="file" name="placeholder_image" accept="image/*" class="file-input file-input-bordered w-full" />
                        @error('placeholder_image')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">Upload Image</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Site Information --}}
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

        {{-- Page Subtitles --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-xl mb-4">Page Subtitles</h2>

                <form action="{{ route('admin.settings.site.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Home Page</span>
                            </label>
                            <textarea name="page_home_subtitle" class="textarea textarea-bordered" rows="2" maxlength="500" placeholder="Subtitle shown on the home page">{{ old('page_home_subtitle', setting('page_home_subtitle', '')) }}</textarea>
                            @error('page_home_subtitle')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Articles Page</span>
                            </label>
                            <textarea name="page_articles_subtitle" class="textarea textarea-bordered" rows="2" maxlength="500" placeholder="Subtitle shown on the articles page">{{ old('page_articles_subtitle', setting('page_articles_subtitle', '')) }}</textarea>
                            @error('page_articles_subtitle')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Photos Page</span>
                            </label>
                            <textarea name="page_photos_subtitle" class="textarea textarea-bordered" rows="2" maxlength="500" placeholder="Subtitle shown on the photos page">{{ old('page_photos_subtitle', setting('page_photos_subtitle', '')) }}</textarea>
                            @error('page_photos_subtitle')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control mt-6">
                            <button type="submit" class="btn btn-primary">Save Subtitles</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Security Alerts --}}
        @if(config('auth.bypass_password_rules'))
            <div class="card bg-error text-error-content shadow">
                <div class="card-body">
                    <h2 class="card-title text-xl">
                        <i class="ph ph-warning-circle text-2xl"></i>
                        Security Warning
                    </h2>
                    <p>Password security rules are currently bypassed. This should only be used in development/testing environments.</p>
                    <p class="text-sm mt-2">Remove <code>BYPASS_PASSWORD_RULES=true</code> from your .env file to enforce strong passwords.</p>
                </div>
            </div>
        @endif

        {{-- Environment --}}
        <div class="card bg-base-100 shadow">
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
                        <div class="stat-value text-lg">v{{ config('app.version') }}</div>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-base-200 rounded-lg">
                    <p class="text-sm text-base-content/60">
                        <i class="ph ph-terminal text-lg mr-2"></i>
                        Use <code class="bg-base-300 px-2 py-1 rounded">php artisan blogwriter:install</code> to reconfigure your site,
                        or edit the <code class="bg-base-300 px-2 py-1 rounded">.env</code> file directly.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-settings-layout>
