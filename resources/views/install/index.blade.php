<x-layouts.install>

<div x-data="installer()" x-init="init()" class="font-mono">
    <div class="mockup-code w-full min-h-[500px]" x-ref="terminal">

        {{-- Accumulated terminal lines --}}
        <template x-for="(line, index) in lines" :key="index">
            <pre :data-prefix="line.prefix || ''" :class="line.class || ''"><code x-text="line.text"></code></pre>
        </template>

        {{-- Welcome Step --}}
        <template x-if="step === 'welcome'">
            <div class="px-6 py-2">
                <pre data-prefix=">"><code class="text-success">Ready to install BlogWriter.</code></pre>
                <pre data-prefix=">"><code>This wizard will set up your IndieWeb-native blog.</code></pre>
                <pre><code>&nbsp;</code></pre>
                <div class="flex gap-2 pl-8">
                    <button @click="startCheck()" class="btn btn-success btn-sm font-mono">Continue</button>
                </div>
            </div>
        </template>

        {{-- Requirements Step --}}
        <template x-if="step === 'requirements'">
            <div class="px-6 py-2">
                <template x-if="!requirementsLoaded">
                    <pre data-prefix="~"><code class="text-info">Checking requirements<span class="loading loading-dots loading-xs"></span></code></pre>
                </template>
                <template x-if="requirementsLoaded">
                    <div>
                        <template x-for="req in requirements" :key="req.name">
                            <pre :data-prefix="req.passed ? '✓' : '✗'" :class="req.passed ? 'text-success' : 'text-error'">
                                <code x-text="req.name + '  [' + req.value + ']'"></code>
                            </pre>
                        </template>
                        <pre><code>&nbsp;</code></pre>
                        <template x-if="allPassed">
                            <div>
                                <pre data-prefix=">" class="text-success"><code>All checks passed!</code></pre>
                                <div class="flex gap-2 pl-8 mt-2">
                                    <button @click="goToAccount()" class="btn btn-success btn-sm font-mono">Continue</button>
                                </div>
                            </div>
                        </template>
                        <template x-if="!allPassed">
                            <div>
                                <pre data-prefix="!" class="text-error"><code>Some requirements are not met. Please fix them and reload.</code></pre>
                                <div class="flex gap-2 pl-8 mt-2">
                                    <button @click="startCheck()" class="btn btn-warning btn-sm font-mono">Retry</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        {{-- Account & Config Step --}}
        <template x-if="step === 'account'">
            <div class="px-6 py-2">
                <pre data-prefix=">"><code class="text-info">Configure your site and admin account.</code></pre>
                <pre><code>&nbsp;</code></pre>

                <form @submit.prevent="submitAccount()" class="space-y-3 pl-8">
                    {{-- Site Name --}}
                    <div>
                        <label class="text-base-content/70 text-sm">site_name:</label>
                        <input type="text" x-model="form.site_name"
                            class="input input-ghost bg-transparent border-b border-base-content/30 rounded-none w-full font-mono focus:outline-none focus:border-success"
                            placeholder="My Blog" required>
                        <template x-if="errors.site_name">
                            <p class="text-error text-xs mt-1" x-text="errors.site_name[0]"></p>
                        </template>
                    </div>

                    {{-- Site URL --}}
                    <div>
                        <label class="text-base-content/70 text-sm">site_url:</label>
                        <input type="url" x-model="form.site_url"
                            class="input input-ghost bg-transparent border-b border-base-content/30 rounded-none w-full font-mono focus:outline-none focus:border-success"
                            placeholder="https://example.com" required>
                        <template x-if="errors.site_url">
                            <p class="text-error text-xs mt-1" x-text="errors.site_url[0]"></p>
                        </template>
                    </div>

                    {{-- Name --}}
                    <div>
                        <label class="text-base-content/70 text-sm">name:</label>
                        <input type="text" x-model="form.name"
                            class="input input-ghost bg-transparent border-b border-base-content/30 rounded-none w-full font-mono focus:outline-none focus:border-success"
                            placeholder="Jane Doe" required>
                        <template x-if="errors.name">
                            <p class="text-error text-xs mt-1" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="text-base-content/70 text-sm">email:</label>
                        <input type="email" x-model="form.email"
                            class="input input-ghost bg-transparent border-b border-base-content/30 rounded-none w-full font-mono focus:outline-none focus:border-success"
                            placeholder="you@example.com" required>
                        <template x-if="errors.email">
                            <p class="text-error text-xs mt-1" x-text="errors.email[0]"></p>
                        </template>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="text-base-content/70 text-sm">password:</label>
                        <div class="flex items-center gap-2">
                            <input type="password" x-model="form.password"
                                class="input input-ghost bg-transparent border-b border-base-content/30 rounded-none w-full font-mono focus:outline-none focus:border-success"
                                placeholder="Min 16 characters" required>
                            <button type="button" @click="generatePassword()" class="btn btn-ghost btn-xs font-mono text-info whitespace-nowrap">
                                Generate passphrase
                            </button>
                        </div>
                        <template x-if="generatedPassphrase">
                            <p class="text-success text-xs mt-1">Generated: <span x-text="generatedPassphrase" class="select-all"></span></p>
                        </template>
                        <template x-if="errors.password">
                            <p class="text-error text-xs mt-1" x-text="errors.password[0]"></p>
                        </template>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="text-base-content/70 text-sm">password_confirmation:</label>
                        <input type="password" x-model="form.password_confirmation"
                            class="input input-ghost bg-transparent border-b border-base-content/30 rounded-none w-full font-mono focus:outline-none focus:border-success"
                            placeholder="Confirm password" required>
                    </div>

                    {{-- Seed Demo --}}
                    <div class="flex items-center gap-3">
                        <label class="text-base-content/70 text-sm">seed_demo:</label>
                        <input type="checkbox" x-model="form.seed_demo" class="checkbox checkbox-success checkbox-sm">
                        <span class="text-base-content/50 text-xs">Include sample articles, photos, and categories</span>
                    </div>

                    <pre><code>&nbsp;</code></pre>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm font-mono" :disabled="submitting">
                            <span x-show="!submitting">Install</span>
                            <span x-show="submitting" class="loading loading-spinner loading-xs"></span>
                        </button>
                    </div>
                </form>
            </div>
        </template>

        {{-- Installing Step --}}
        <template x-if="step === 'installing'">
            <div class="px-6 py-2">
                <template x-if="!installComplete">
                    <pre data-prefix="~"><code class="text-info">Installing<span class="loading loading-dots loading-xs"></span></code></pre>
                </template>
            </div>
        </template>

        {{-- Seeding Step --}}
        <template x-if="step === 'seeding'">
            <div class="px-6 py-2">
                <template x-if="!seedComplete">
                    <pre data-prefix="~"><code class="text-info">Seeding demo content (this may take 30-60 seconds)<span class="loading loading-dots loading-xs"></span></code></pre>
                </template>
            </div>
        </template>

        {{-- Complete Step --}}
        <template x-if="step === 'complete'">
            <div class="px-6 py-2">
                <pre data-prefix="✓" class="text-success"><code>Installation complete!</code></pre>
                <pre><code>&nbsp;</code></pre>
                <pre data-prefix=">"><code x-text="'Site URL:  ' + resultUrls.siteUrl"></code></pre>
                <pre data-prefix=">"><code x-text="'Admin URL: ' + resultUrls.adminUrl"></code></pre>
                <pre><code>&nbsp;</code></pre>
                <pre data-prefix=">"><code>Happy blogging! Own your content. Own your domain.</code></pre>
                <pre><code>&nbsp;</code></pre>
                <div class="flex gap-2 pl-8">
                    <a :href="resultUrls.siteUrl" class="btn btn-success btn-sm font-mono">Visit Site</a>
                    <a :href="resultUrls.adminUrl" class="btn btn-ghost btn-sm font-mono">Go to Admin</a>
                </div>
            </div>
        </template>

    </div>
</div>

<script>
function installer() {
    return {
        step: 'welcome',
        lines: [],
        requirements: [],
        allPassed: false,
        requirementsLoaded: false,
        submitting: false,
        installComplete: false,
        seedComplete: false,
        generatedPassphrase: null,
        resultUrls: { siteUrl: '', adminUrl: '' },
        errors: {},

        form: {
            site_name: '',
            site_url: window.location.origin,
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            seed_demo: false,
        },

        init() {
            this.addLine('$', 'blogwriter install', 'text-success');
            this.addLine('', '');
            this.addLine('>', '╔════════════════════════════════════════╗');
            this.addLine('>', '║     Welcome to BlogWriter Installer    ║');
            this.addLine('>', '╚════════════════════════════════════════╝');
            this.addLine('>', 'Own Your Content. Own Your Domain.');
            this.addLine('', '');
        },

        addLine(prefix, text, className) {
            this.lines.push({ prefix, text, class: className || '' });
            this.$nextTick(() => this.scrollToBottom());
        },

        scrollToBottom() {
            if (this.$refs.terminal) {
                this.$refs.terminal.scrollTop = this.$refs.terminal.scrollHeight;
            }
        },

        async startCheck() {
            this.step = 'requirements';
            this.requirementsLoaded = false;
            this.addLine('$', 'check requirements', 'text-success');

            try {
                const response = await fetch('{{ route("install.check") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();
                this.requirements = data.requirements;
                this.allPassed = data.allPassed;
                this.requirementsLoaded = true;
            } catch (error) {
                this.addLine('!', 'Failed to check requirements: ' + error.message, 'text-error');
            }
        },

        goToAccount() {
            this.addLine('✓', 'Requirements passed', 'text-success');
            this.addLine('', '');
            this.addLine('$', 'configure', 'text-success');
            this.step = 'account';
        },

        async generatePassword() {
            try {
                const response = await fetch('{{ route("install.passphrase") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();
                this.generatedPassphrase = data.passphrase;
                this.form.password = data.passphrase;
                this.form.password_confirmation = data.passphrase;
            } catch (error) {
                this.addLine('!', 'Failed to generate passphrase', 'text-error');
            }
        },

        async submitAccount() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("install.account") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.addLine('!', data.message || 'Validation failed', 'text-error');
                    }
                    this.submitting = false;
                    return;
                }

                this.addLine('✓', 'Configuration saved', 'text-success');
                this.addLine('', '');
                await this.runFinalize();
            } catch (error) {
                this.addLine('!', 'Request failed: ' + error.message, 'text-error');
                this.submitting = false;
            }
        },

        async runFinalize() {
            this.step = 'installing';
            this.addLine('$', 'install --finalize', 'text-success');

            const steps = [
                'Ensuring storage directories...',
                'Creating storage link...',
                'Setting up environment file...',
                'Generating application key...',
                'Updating environment configuration...',
                'Running database migrations...',
                'Creating admin user...',
                'Clearing caches...',
                'Creating lock file...',
            ];

            for (const stepText of steps) {
                this.addLine('~', stepText, 'text-info');
                await this.delay(200);
            }

            try {
                const response = await fetch('{{ route("install.finalize") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    this.addLine('!', data.message || 'Installation failed', 'text-error');
                    this.submitting = false;
                    return;
                }

                this.installComplete = true;
                this.resultUrls = { siteUrl: data.siteUrl, adminUrl: data.adminUrl };
                this.addLine('✓', 'Installation finalized', 'text-success');
                this.addLine('', '');

                if (this.form.seed_demo) {
                    await this.runSeed();
                } else {
                    this.step = 'complete';
                }
            } catch (error) {
                this.addLine('!', 'Installation failed: ' + error.message, 'text-error');
                this.submitting = false;
            }
        },

        async runSeed() {
            this.step = 'seeding';
            this.addLine('$', 'seed --demo', 'text-success');
            this.addLine('~', 'Seeding demo content (this may take 30-60 seconds)...', 'text-info');

            try {
                const response = await fetch('{{ route("install.seed") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    this.addLine('!', data.message || 'Seeding failed', 'text-error');
                    this.addLine('>', 'Installation succeeded but demo content was not added.', 'text-warning');
                } else {
                    this.seedComplete = true;
                    this.addLine('✓', 'Demo content added', 'text-success');
                }
            } catch (error) {
                this.addLine('!', 'Seeding failed: ' + error.message, 'text-error');
                this.addLine('>', 'Installation succeeded but demo content was not added.', 'text-warning');
            }

            this.addLine('', '');
            this.step = 'complete';
        },

        delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },
    };
}
</script>

</x-layouts.install>
