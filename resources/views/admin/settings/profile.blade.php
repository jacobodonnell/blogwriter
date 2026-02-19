<x-settings-layout active="profile">
    <form action="{{ route('admin.settings.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Identity Card --}}
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title font-admin">
                    <i class="ph ph-user text-xl"></i>
                    Identity
                </h2>
                <p class="text-sm text-base-content/60 font-admin">Your public name, avatar, and bio shown on the About page.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-admin font-medium">Name</span>
                        </label>
                        <input type="text" name="profile_name" class="input input-bordered" value="{{ old('profile_name', auth()->user()->name) }}" required />
                        @error('profile_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-admin font-medium">Avatar URL</span>
                        </label>
                        <input type="url" name="profile_avatar" class="input input-bordered" value="{{ old('profile_avatar', setting('profile_avatar', '')) }}" placeholder="https://example.com/avatar.jpg" />
                        @error('profile_avatar')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text font-admin font-medium">Bio</span>
                    </label>
                    <textarea name="profile_bio" class="textarea textarea-bordered" rows="3">{{ old('profile_bio', setting('profile_bio', '')) }}</textarea>
                    <p class="text-xs text-base-content/50 mt-1 font-admin">Supports Markdown (no headings)</p>
                    @error('profile_bio')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Social Links Card --}}
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title font-admin">
                    <i class="ph ph-share-network text-xl"></i>
                    Social Links
                </h2>
                <p class="text-sm text-base-content/60 font-admin">Your social profiles and contact links displayed on the About page.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-admin font-medium">GitHub URL</span>
                        </label>
                        <input type="url" name="profile_github" class="input input-bordered" value="{{ old('profile_github', setting('profile_github', '')) }}" placeholder="https://github.com/username" />
                        @error('profile_github')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-admin font-medium">Mastodon URL</span>
                        </label>
                        <input type="url" name="profile_mastodon" class="input input-bordered" value="{{ old('profile_mastodon', setting('profile_mastodon', '')) }}" placeholder="https://mastodon.social/@username" />
                        @error('profile_mastodon')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-admin font-medium">Bluesky URL</span>
                        </label>
                        <input type="url" name="profile_bluesky" class="input input-bordered" value="{{ old('profile_bluesky', setting('profile_bluesky', '')) }}" placeholder="https://bsky.app/profile/username" />
                        @error('profile_bluesky')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-admin font-medium">Email</span>
                        </label>
                        <input type="email" name="profile_email" class="input input-bordered" value="{{ old('profile_email', setting('profile_email', '')) }}" placeholder="you@example.com" />
                        @error('profile_email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary font-admin">
            <i class="ph ph-floppy-disk text-lg"></i>
            Save Profile
        </button>
    </form>
</x-settings-layout>
