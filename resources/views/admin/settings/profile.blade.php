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
                    <div>
                        <label for="profile_name" class="block text-base font-medium font-admin mb-1">
                            <i class="ph ph-user-circle"></i>
                            Name
                        </label>
                        <input id="profile_name" type="text" name="profile_name" class="input w-full" value="{{ old('profile_name', auth()->user()->name) }}" required />
                        @error('profile_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="profile_avatar" class="block text-base font-medium font-admin mb-1">
                            <i class="ph ph-image-square"></i>
                            Avatar URL
                        </label>
                        <input id="profile_avatar" type="url" name="profile_avatar" class="input w-full" value="{{ old('profile_avatar', setting('profile_avatar', '')) }}" placeholder="https://example.com/avatar.jpg" />
                        @error('profile_avatar')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-data="bioEditor({ content: @js(old('profile_bio', setting('profile_bio', ''))) })">
                        <label class="block text-base font-medium font-admin mb-1">
                            <i class="ph ph-text-aa"></i>
                            Bio
                        </label>
                        <input type="hidden" name="profile_bio" :value="content">

                        {{-- Toolbar --}}
                        <div class="tiptap-toolbar flex items-center gap-1 p-2 bg-base-200 border border-base-content/20 border-b-0 rounded-t-field">
                            <button type="button" @click="command('bold')" :class="isActive('bold') && 'btn-active'"
                                    class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Bold">
                                <i class="ph ph-text-b"></i>
                            </button>
                            <button type="button" @click="command('italic')" :class="isActive('italic') && 'btn-active'"
                                    class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Italic">
                                <i class="ph ph-text-italic"></i>
                            </button>
                            <div class="divider divider-horizontal mx-0"></div>
                            <button type="button" @click="command('h2')" :class="isActive('heading', { level: 2 }) && 'btn-active'"
                                    class="btn btn-ghost btn-xs tooltip" data-tip="Heading">
                                H2
                            </button>
                            <div class="divider divider-horizontal mx-0"></div>
                            <button type="button" @click="command('link')" :class="isActive('link') && 'btn-active'"
                                    class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Link">
                                <i class="ph ph-link"></i>
                            </button>
                        </div>

                        {{-- Link dialog --}}
                        <div x-show="showLinkDialog" x-cloak class="flex items-center gap-2 px-2 py-1.5 bg-base-200 border border-base-content/20 border-t-0 border-b-0">
                            <input type="url" x-model="linkUrl" @keydown.enter.prevent="insertLink()" placeholder="https://example.com"
                                   class="input input-sm input-bordered grow" />
                            <button type="button" @click="insertLink()" class="btn btn-sm btn-primary">Insert</button>
                            <button type="button" @click="showLinkDialog = false" class="btn btn-sm btn-ghost">Cancel</button>
                        </div>

                        {{-- Editor --}}
                        <div x-ref="bioEditor" class="tiptap-editor border border-base-content/20 rounded-b-field min-h-40"></div>

                        @error('profile_bio')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
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
                    <div>
                        <label for="profile_github" class="block text-base font-medium font-admin mb-1">
                            <i class="ph ph-github-logo"></i>
                            GitHub URL
                        </label>
                        <input id="profile_github" type="url" name="profile_github" class="input w-full" value="{{ old('profile_github', setting('profile_github', '')) }}" placeholder="https://github.com/username" />
                        @error('profile_github')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="profile_mastodon" class="block text-base font-medium font-admin mb-1">
                            <i class="ph ph-mastodon-logo"></i>
                            Mastodon URL
                        </label>
                        <input id="profile_mastodon" type="url" name="profile_mastodon" class="input w-full" value="{{ old('profile_mastodon', setting('profile_mastodon', '')) }}" placeholder="https://mastodon.social/@username" />
                        @error('profile_mastodon')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="profile_bluesky" class="block text-base font-medium font-admin mb-1">
                            <i class="ph ph-butterfly"></i>
                            Bluesky URL
                        </label>
                        <input id="profile_bluesky" type="url" name="profile_bluesky" class="input w-full" value="{{ old('profile_bluesky', setting('profile_bluesky', '')) }}" placeholder="https://bsky.app/profile/username" />
                        @error('profile_bluesky')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="profile_email" class="block text-base font-medium font-admin mb-1">
                            <i class="ph ph-envelope"></i>
                            Email
                        </label>
                        <input id="profile_email" type="email" name="profile_email" class="input w-full" value="{{ old('profile_email', setting('profile_email', '')) }}" placeholder="you@example.com" />
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
