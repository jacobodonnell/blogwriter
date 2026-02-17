<x-layouts.admin>
    @section('title', 'Settings')

    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold">Settings</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Configure your BlogWriter site.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Appearance Settings Link --}}
            <a href="{{ route('admin.settings.appearance') }}" class="card bg-base-100 shadow hover:shadow-md transition-shadow lg:col-span-2">
                <div class="card-body flex-row items-center gap-4">
                    <div class="bg-primary/10 p-3 rounded-lg">
                        <i class="ph ph-palette text-2xl text-primary"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="card-title text-lg">Appearance</h2>
                        <p class="text-sm text-base-content/60">Customize your site's theme and font.</p>
                    </div>
                    <i class="ph ph-caret-right text-xl text-base-content/40"></i>
                </div>
            </a>

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

            {{-- Page Subtitles --}}
            <div class="card bg-base-100 shadow lg:col-span-2">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Page Subtitles</h2>

                    @if(session('subtitles_success'))
                        <div class="alert alert-success mb-4">
                            <i class="ph ph-check-circle text-xl"></i>
                            <span>{{ session('subtitles_success') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.pages.update') }}" method="POST">
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

            {{-- User Profile --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Your Profile</h2>

                    @if(session('profile_success'))
                        <div class="alert alert-success mb-4">
                            <i class="ph ph-check-circle text-xl"></i>
                            <span>{{ session('profile_success') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Name</span>
                                </label>
                                <input type="text" name="profile_name" class="input input-bordered" value="{{ old('profile_name', auth()->user()->name) }}" required />
                                @error('profile_name')
                                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Bio</span>
                                </label>
                                <textarea name="profile_bio" class="textarea textarea-bordered" rows="3">{{ old('profile_bio', setting('profile_bio', '')) }}</textarea>
                                @error('profile_bio')
                                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Avatar URL</span>
                                </label>
                                <input type="url" name="profile_avatar" class="input input-bordered" value="{{ old('profile_avatar', setting('profile_avatar', '')) }}" placeholder="https://example.com/avatar.jpg" />
                                @error('profile_avatar')
                                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">GitHub URL</span>
                                </label>
                                <input type="url" name="profile_github" class="input input-bordered" value="{{ old('profile_github', setting('profile_github', '')) }}" placeholder="https://github.com/username" />
                                @error('profile_github')
                                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Mastodon URL</span>
                                </label>
                                <input type="url" name="profile_mastodon" class="input input-bordered" value="{{ old('profile_mastodon', setting('profile_mastodon', '')) }}" placeholder="https://mastodon.social/@username" />
                                @error('profile_mastodon')
                                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Bluesky URL</span>
                                </label>
                                <input type="url" name="profile_bluesky" class="input input-bordered" value="{{ old('profile_bluesky', setting('profile_bluesky', '')) }}" placeholder="https://bsky.app/profile/username" />
                                @error('profile_bluesky')
                                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Email</span>
                                </label>
                                <input type="email" name="profile_email" class="input input-bordered" value="{{ old('profile_email', setting('profile_email', '')) }}" placeholder="you@example.com" />
                                @error('profile_email')
                                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control mt-6">
                                <button type="submit" class="btn btn-primary">Save Profile</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Security Alerts --}}
            @if(config('auth.bypass_password_rules'))
                <div class="card bg-error text-error-content shadow lg:col-span-2">
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

            {{-- Password Requirements --}}
            <div class="card bg-base-100 shadow lg:col-span-2">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">
                        <i class="ph ph-shield-check text-xl"></i>
                        Password Requirements
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-check-circle text-success text-xl"></i>
                            <span>Minimum 16 characters</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="ph ph-check-circle text-success text-xl"></i>
                            <span>At least one letter</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="ph ph-check-circle text-success text-xl"></i>
                            <span>At least one number</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="ph ph-check-circle text-success text-xl"></i>
                            <span>At least one symbol</span>
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
