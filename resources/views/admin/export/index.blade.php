<x-settings-layout active="export">
    <div class="space-y-6">

        {{-- Import Card --}}
        <div class="card bg-base-100 shadow"
             x-data="{
                state: 'idle',
                token: null,
                missing: [],
                duplicateStrategy: 'skip',
                fileInput: null,

                async submitImport(event) {
                    event.preventDefault();
                    const form = event.target;
                    const file = form.querySelector('input[type=file]').files[0];
                    if (!file) {
                        $dispatch('toast:show', { message: 'Please select a ZIP file to import.', type: 'error' });
                        return;
                    }

                    this.state = 'uploading';
                    const data = new FormData(form);

                    try {
                        const res = await fetch('{{ route('admin.import.articles') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: data,
                        });
                        const json = await res.json();

                        if (json.status === 'preflight_warning') {
                            this.missing = json.missing;
                            this.token = json.token;
                            this.state = 'warning';
                        } else if (json.status === 'ok') {
                            this.handleSuccess(json);
                        } else {
                            this.handleError(json.message ?? 'Import failed.');
                        }
                    } catch (e) {
                        this.handleError('An unexpected error occurred.');
                    }
                },

                async confirmImport() {
                    this.state = 'uploading';

                    try {
                        const res = await fetch('{{ route('admin.import.articles.confirm') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ token: this.token, duplicate_strategy: this.duplicateStrategy }),
                        });
                        const json = await res.json();

                        if (json.status === 'ok') {
                            this.handleSuccess(json);
                        } else {
                            this.handleError(json.message ?? 'Import failed.');
                        }
                    } catch (e) {
                        this.handleError('An unexpected error occurred.');
                    }
                },

                handleSuccess(json) {
                    $dispatch('toast:show', {
                        message: 'Site data imported. ' + json.imported + ' article' + (json.imported !== 1 ? 's' : '') + ' imported, ' + json.skipped + ' skipped.',
                        type: 'success',
                    });
                    this.reset();
                },

                handleError(message) {
                    $dispatch('toast:show', { message: message, type: 'error' });
                    this.reset();
                },

                reset() {
                    this.state = 'idle';
                    this.token = null;
                    this.missing = [];
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },
             }">
            <div class="card-body">
                <h2 class="card-title font-admin">
                    <i class="ph ph-upload-simple text-xl"></i>
                    Import Site Data
                </h2>
                <p class="text-sm text-base-content/60 font-admin">
                    Upload a BlogWriter ZIP export to restore your full site — articles, photos, categories, and settings.
                    Existing content with matching slugs will be skipped or overwritten based on your choice.
                </p>

                {{-- Idle / Upload form --}}
                <form @submit="submitImport($event)" class="mt-4 space-y-4" x-show="state !== 'warning'">
                    <div class="form-control">
                        <input
                            type="file"
                            name="file"
                            accept=".zip"
                            x-ref="fileInput"
                            class="file-input file-input-bordered w-full max-w-sm font-admin"
                            :disabled="state === 'uploading'"
                            data-test="import-file-input"
                        >
                    </div>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend font-admin text-xs">Duplicate content</legend>
                        <label class="label gap-2 cursor-pointer justify-start font-admin text-sm">
                            <input type="radio" name="duplicate_strategy" value="skip" class="radio radio-sm" x-model="duplicateStrategy">
                            <span>Skip — keep existing content unchanged</span>
                        </label>
                        <label class="label gap-2 cursor-pointer justify-start font-admin text-sm">
                            <input type="radio" name="duplicate_strategy" value="overwrite" class="radio radio-sm" x-model="duplicateStrategy">
                            <span>Overwrite — replace existing content with imported data</span>
                        </label>
                    </fieldset>

                    <button
                        type="submit"
                        class="btn btn-primary font-admin"
                        :disabled="state === 'uploading'"
                        data-test="import-articles-btn"
                    >
                        <span x-show="state !== 'uploading'">
                            <i class="ph ph-upload-simple text-lg"></i>
                            Import Site Data
                        </span>
                        <span x-show="state === 'uploading'" x-cloak class="flex items-center gap-2">
                            <span class="loading loading-spinner loading-sm"></span>
                            Importing&hellip;
                        </span>
                    </button>
                </form>

                {{-- Preflight warning --}}
                <div x-show="state === 'warning'" x-cloak class="mt-4 space-y-4">
                    <div role="alert" class="alert alert-warning font-admin">
                        <i class="ph ph-warning text-xl"></i>
                        <div>
                            <p class="font-semibold" x-text="missing.length + ' categor' + (missing.length === 1 ? 'y' : 'ies') + ' weren\'t found in BlogWriter.'"></p>
                            <div class="flex flex-wrap gap-1 mt-2">
                                <template x-for="slug in missing" :key="slug">
                                    <span class="badge badge-warning font-mono text-xs" x-text="slug"></span>
                                </template>
                            </div>
                            <p class="mt-2 text-sm">These articles will be imported with no category assigned.</p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="btn btn-primary font-admin"
                            @click="confirmImport()"
                            :disabled="state === 'uploading'"
                        >
                            <span x-show="state !== 'uploading'">Proceed with Import</span>
                            <span x-show="state === 'uploading'" x-cloak class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-sm"></span>
                                Importing&hellip;
                            </span>
                        </button>
                        <button
                            type="button"
                            class="btn btn-ghost font-admin"
                            @click="reset()"
                            :disabled="state === 'uploading'"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Export Card --}}
        <div class="card bg-base-100 shadow"
             x-data="{
                exporting: false,
                startExport() {
                    this.exporting = true;
                    setTimeout(() => { this.exporting = false; }, 1500);
                }
             }">
            <div class="card-body">
                <h2 class="card-title font-admin">
                    <i class="ph ph-file-text text-xl"></i>
                    Export Site Data
                </h2>
                <p class="text-sm text-base-content/60 font-admin">
                    Download a full backup of your site as a ZIP — includes all {{ $articleCount }} {{ Str::plural('article', $articleCount) }},
                    photos, categories, and settings.
                </p>

                <form method="POST" action="{{ route('admin.export.articles') }}" @submit="startExport()" class="mt-4">
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-primary font-admin"
                        :disabled="exporting"
                        data-test="export-articles-btn"
                    >
                        <span x-show="!exporting">
                            <i class="ph ph-download-simple text-lg"></i>
                            Export Site Data
                        </span>
                        <span x-show="exporting" x-cloak class="flex items-center gap-2">
                            <span class="loading loading-spinner loading-sm"></span>
                            Generating ZIP&hellip;
                        </span>
                    </button>
                </form>
            </div>
        </div>

        {{-- About the export format --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title font-admin text-base">
                    <i class="ph ph-info text-xl"></i>
                    About the Export Format
                </h2>
                <div class="text-sm text-base-content/70 font-admin space-y-2">
                    <p>
                        Each article is exported as a Markdown file with YAML frontmatter — a widely-supported format
                        compatible with <strong>Hugo</strong>, <strong>Jekyll</strong>, <strong>Eleventy (11ty)</strong>,
                        and most other static site generators.
                    </p>
                    <p>
                        Frontmatter includes: <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">title</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">date</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">slug</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">draft</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">category</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">past_slugs</code>, and SEO fields.
                    </p>
                    <p>
                        The ZIP also includes <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">settings.yaml</code>
                        with your appearance preferences (theme, font), profile details (name, bio, social links), and page subtitles.
                        These are restored automatically when importing into a fresh BlogWriter installation.
                    </p>
                    <p>
                        Photos and their original image files are included in
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">photos.yaml</code>
                        + <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">images/</code>.
                        Conversions (thumbnail, medium, large) are regenerated automatically on import.
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-settings-layout>
