<x-settings-layout active="profile">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-xl mb-4">Your Profile</h2>

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
</x-settings-layout>
