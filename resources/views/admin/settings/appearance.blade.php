<x-layouts.admin>
    @section('title', 'Appearance')

    @section('breadcrumb')
        <li><a href="{{ route('admin.settings') }}">Settings</a></li>
        <li>Appearance</li>
    @endsection

    @section('page-title', 'Appearance')
    @section('page-subtitle', 'Customize your site\'s theme and font.')

    <div x-data="{
            lightTheme: '{{ $currentLight }}',
            darkTheme: '{{ $currentDark }}',
            font: '{{ $currentFont }}',
            savedLight: '{{ $currentLight }}',
            savedDark: '{{ $currentDark }}',
            savedFont: '{{ $currentFont }}',
            fontSizeScales: {{ Js::from($fontSizeScales) }},
            lightOpen: false,
            darkOpen: false,
            fontOpen: false,
            previewing: false,

            get resolvedTheme() {
                const mode = localStorage.getItem('themeMode') || 'system';
                const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (mode === 'dark') return this.darkTheme;
                if (mode === 'light') return this.lightTheme;
                return systemDark ? this.darkTheme : this.lightTheme;
            },

            scaleForFont(key) {
                return this.fontSizeScales[key] ?? 1;
            },

            previewTheme(theme) {
                this.previewing = true;
                document.documentElement.setAttribute('data-theme', theme);
            },

            revertTheme() {
                this.previewing = false;
                document.documentElement.setAttribute('data-theme', this.resolvedTheme);
            },

            selectLight(theme) {
                this.lightTheme = theme;
                this.lightOpen = false;
                this.revertTheme();
            },

            selectDark(theme) {
                this.darkTheme = theme;
                this.darkOpen = false;
                this.revertTheme();
            },

            previewFont(key) {
                this.previewing = true;
                document.documentElement.style.setProperty('--font-sans', 'var(--font-' + key + ')');
                document.documentElement.style.setProperty('--font-size-scale', this.scaleForFont(key));
            },

            revertFont() {
                this.previewing = false;
                document.documentElement.style.setProperty('--font-sans', 'var(--font-' + this.font + ')');
                document.documentElement.style.setProperty('--font-size-scale', this.scaleForFont(this.font));
            },

            selectFont(key) {
                this.font = key;
                this.fontOpen = false;
                this.revertFont();
            },

            init() {
                document.documentElement.setAttribute('data-theme', this.resolvedTheme);
            }
        }"
        @click.outside="lightOpen = false; darkOpen = false; fontOpen = false; if (previewing) { revertTheme(); revertFont(); }"
        class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Left Column: Selectors --}}
        <div class="xl:col-span-2 space-y-6">
            <form method="POST" action="{{ route('admin.settings.appearance.update') }}">
                @csrf
                @method('PUT')

                {{-- Light Theme Dropdown --}}
                <div class="card bg-base-100 shadow mb-6">
                    <div class="card-body">
                        <h2 class="card-title font-admin">
                            <i class="ph ph-sun text-xl"></i>
                            Light Theme
                        </h2>
                        <p class="text-sm text-base-content/60 font-admin">Used when visitors or you select light mode.</p>

                        <input type="hidden" name="theme_light" :value="lightTheme">

                        <div class="relative mt-3">
                            <button type="button"
                                    data-test="light-theme-toggle"
                                    @click="lightOpen = !lightOpen; darkOpen = false; fontOpen = false"
                                    class="btn btn-outline w-full justify-between font-admin">
                                <span x-text="lightTheme" class="capitalize" data-test="light-theme-label"></span>
                                <i class="ph ph-caret-down text-sm" :class="lightOpen && 'rotate-180'" style="transition: transform 0.2s"></i>
                            </button>

                            <div x-show="lightOpen" x-transition x-cloak
                                 data-test="light-theme-dropdown"
                                 @click.outside="lightOpen = false; revertTheme()"
                                 class="absolute z-50 mt-1 w-full max-h-72 overflow-y-auto rounded-box bg-base-100 border border-base-300 shadow-lg">
                                @foreach($lightThemes as $theme)
                                    <button type="button"
                                            data-test="light-option-{{ $theme }}"
                                            @mouseenter="previewTheme('{{ $theme }}')"
                                            @mouseleave="revertTheme()"
                                            @click="selectLight('{{ $theme }}')"
                                            :class="lightTheme === '{{ $theme }}' && 'bg-primary/10 font-semibold'"
                                            class="w-full flex items-center gap-3 px-4 py-2 hover:bg-base-200 text-left font-admin">
                                        {{-- DaisyUI color swatches --}}
                                        <span class="flex gap-0.5" data-theme="{{ $theme }}">
                                            <span class="w-3 h-3 rounded-full bg-primary"></span>
                                            <span class="w-3 h-3 rounded-full bg-secondary"></span>
                                            <span class="w-3 h-3 rounded-full bg-accent"></span>
                                        </span>
                                        <span class="capitalize">{{ $theme }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('theme_light')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dark Theme Dropdown --}}
                <div class="card bg-base-100 shadow mb-6">
                    <div class="card-body">
                        <h2 class="card-title font-admin">
                            <i class="ph ph-moon text-xl"></i>
                            Dark Theme
                        </h2>
                        <p class="text-sm text-base-content/60 font-admin">Used when visitors or you select dark mode.</p>

                        <input type="hidden" name="theme_dark" :value="darkTheme">

                        <div class="relative mt-3">
                            <button type="button"
                                    data-test="dark-theme-toggle"
                                    @click="darkOpen = !darkOpen; lightOpen = false; fontOpen = false"
                                    class="btn btn-outline w-full justify-between font-admin">
                                <span x-text="darkTheme" class="capitalize" data-test="dark-theme-label"></span>
                                <i class="ph ph-caret-down text-sm" :class="darkOpen && 'rotate-180'" style="transition: transform 0.2s"></i>
                            </button>

                            <div x-show="darkOpen" x-transition x-cloak
                                 data-test="dark-theme-dropdown"
                                 @click.outside="darkOpen = false; revertTheme()"
                                 class="absolute z-50 mt-1 w-full max-h-72 overflow-y-auto rounded-box bg-base-100 border border-base-300 shadow-lg">
                                @foreach($darkThemes as $theme)
                                    <button type="button"
                                            data-test="dark-option-{{ $theme }}"
                                            @mouseenter="previewTheme('{{ $theme }}')"
                                            @mouseleave="revertTheme()"
                                            @click="selectDark('{{ $theme }}')"
                                            :class="darkTheme === '{{ $theme }}' && 'bg-primary/10 font-semibold'"
                                            class="w-full flex items-center gap-3 px-4 py-2 hover:bg-base-200 text-left font-admin">
                                        <span class="flex gap-0.5" data-theme="{{ $theme }}">
                                            <span class="w-3 h-3 rounded-full bg-primary"></span>
                                            <span class="w-3 h-3 rounded-full bg-secondary"></span>
                                            <span class="w-3 h-3 rounded-full bg-accent"></span>
                                        </span>
                                        <span class="capitalize">{{ $theme }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('theme_dark')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Font Dropdown --}}
                <div class="card bg-base-100 shadow mb-6">
                    <div class="card-body">
                        <h2 class="card-title font-admin">
                            <i class="ph ph-text-aa text-xl"></i>
                            Font
                        </h2>
                        <p class="text-sm text-base-content/60 font-admin">The font used for your site's content. Admin controls always use Instrument Sans.</p>

                        <input type="hidden" name="theme_font" :value="font">

                        <div class="relative mt-3">
                            <button type="button"
                                    data-test="font-toggle"
                                    @click="fontOpen = !fontOpen; lightOpen = false; darkOpen = false"
                                    class="btn btn-outline w-full justify-between font-admin">
                                <span x-text="{{ Js::from($fonts) }}[font]" class="font-admin" data-test="font-label"></span>
                                <i class="ph ph-caret-down text-sm" :class="fontOpen && 'rotate-180'" style="transition: transform 0.2s"></i>
                            </button>

                            <div x-show="fontOpen" x-transition x-cloak
                                 data-test="font-dropdown"
                                 @click.outside="fontOpen = false; revertFont()"
                                 class="absolute z-50 mt-1 w-full max-h-72 overflow-y-auto rounded-box bg-base-100 border border-base-300 shadow-lg">
                                @foreach($fontCategories as $category => $keys)
                                    <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-base-content/40 font-admin">
                                        {{ $category }}
                                    </div>
                                    @foreach($keys as $key)
                                        <button type="button"
                                                data-test="font-option-{{ $key }}"
                                                @mouseenter="previewFont('{{ $key }}')"
                                                @mouseleave="revertFont()"
                                                @click="selectFont('{{ $key }}')"
                                                :class="font === '{{ $key }}' && 'bg-primary/10'"
                                                class="w-full flex items-center justify-between px-4 py-2 hover:bg-base-200 text-left">
                                            <span style="font-family: var(--font-{{ $key }})">{{ $fonts[$key] }}</span>
                                            <span x-show="font === '{{ $key }}'" class="text-primary font-admin text-sm" x-cloak>
                                                <i class="ph ph-check"></i>
                                            </span>
                                        </button>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                        @error('theme_font')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Save Button --}}
                <button type="submit" data-test="save-appearance" class="btn btn-primary w-full font-admin">
                    <i class="ph ph-floppy-disk text-lg"></i>
                    Save Appearance
                </button>
            </form>
        </div>

        {{-- Right Column: Live Preview --}}
        <div class="xl:col-span-1">
            <div class="card bg-base-100 shadow sticky top-20">
                <div class="card-body">
                    <h2 class="card-title font-admin text-sm uppercase tracking-wider text-base-content/50">
                        <i class="ph ph-eye text-lg"></i>
                        Preview
                    </h2>

                    <div class="space-y-4 mt-2">
                        <h3 class="text-2xl font-bold" data-test="preview-heading">Hello, World!</h3>
                        <p class="text-base-content/80">
                            This is a preview of how your site will look with the selected theme and font. Hover over options to see changes in real time.
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm">Primary</button>
                            <button type="button" class="btn btn-secondary btn-sm">Secondary</button>
                            <button type="button" class="btn btn-accent btn-sm">Accent</button>
                        </div>

                        <div class="divider"></div>

                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-neutral text-neutral-content w-10 rounded-full">
                                    <span>JD</span>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Jane Doe</p>
                                <p class="text-xs text-base-content/60">Published 2 min ago</p>
                            </div>
                        </div>

                        <div class="mockup-code text-xs">
                            <pre data-prefix="$"><code>blogwriter serve</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
