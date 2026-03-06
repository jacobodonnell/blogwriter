<x-settings-layout active="customizer">
    <form method="POST" action="{{ route('admin.settings.customizer.update') }}">
        @csrf
        @method('PUT')

        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title font-admin">
                    <i class="ph ph-sliders-horizontal text-xl"></i>
                    Default Editor Mode
                </h2>
                <p class="text-sm text-base-content/60 font-admin">
                    Choose the default layout when opening the article editor. This setting syncs across all your browsers. The editor also remembers the last mode you used &mdash; so if you switch modes from the toolbar, that choice will stick until you change it here again.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    {{-- Fullscreen --}}
                    <label class="card bg-base-200 shadow-sm cursor-pointer transition-all hover:shadow-md"
                           data-test="mode-fullscreen">
                        <input type="radio" name="customizer_editor_mode" value="fullscreen"
                               class="hidden peer"
                               @checked($currentMode === 'fullscreen')>
                        <div class="card-body items-center text-center peer-checked:ring-2 peer-checked:ring-primary rounded-box">
                            <i class="ph ph-arrows-out text-3xl text-primary"></i>
                            <h3 class="font-semibold font-admin mt-2">Fullscreen</h3>
                            <p class="text-xs text-base-content/60 font-admin">
                                Distraction-free writing. Full-width editor, settings and revisions slide in from the right.
                            </p>
                        </div>
                    </label>

                    {{-- Classic --}}
                    <label class="card bg-base-200 shadow-sm cursor-pointer transition-all hover:shadow-md"
                           data-test="mode-classic">
                        <input type="radio" name="customizer_editor_mode" value="classic"
                               class="hidden peer"
                               @checked($currentMode === 'classic')>
                        <div class="card-body items-center text-center peer-checked:ring-2 peer-checked:ring-primary rounded-box">
                            <i class="ph ph-frame-corners text-3xl text-primary"></i>
                            <h3 class="font-semibold font-admin mt-2">Classic</h3>
                            <p class="text-xs text-base-content/60 font-admin">
                                Side-by-side layout with editor and settings panel.
                            </p>
                        </div>
                    </label>

                    {{-- Split Panel --}}
                    <label class="card bg-base-200 shadow-sm cursor-pointer transition-all hover:shadow-md"
                           data-test="mode-split">
                        <input type="radio" name="customizer_editor_mode" value="split"
                               class="hidden peer"
                               @checked($currentMode === 'split')>
                        <div class="card-body items-center text-center peer-checked:ring-2 peer-checked:ring-primary rounded-box">
                            <i class="ph ph-split-horizontal text-3xl text-primary"></i>
                            <h3 class="font-semibold font-admin mt-2">Split Panel</h3>
                            <p class="text-xs text-base-content/60 font-admin">
                                Editor panel on the left with a live preview on the right.
                            </p>
                        </div>
                    </label>
                </div>

                @error('customizer_editor_mode')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit" data-test="save-customizer" class="btn btn-primary w-full font-admin">
            <i class="ph ph-floppy-disk text-lg"></i>
            Save Customizer Settings
        </button>
    </form>
</x-settings-layout>
