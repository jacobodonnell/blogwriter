<x-layouts.public title="About - {{ config('app.name', 'BlogWriter') }}">

    <div class="max-w-3xl mx-auto">
        
        {{-- Breadcrumbs --}}
        <nav class="text-sm breadcrumbs mb-6">
            <ul>
                <li><a href="/" class="link link-hover">Home</a></li>
                <li class="text-base-content/60">About</li>
            </ul>
        </nav>

        {{-- About Header --}}
        <header class="mb-12">
            <h1 class="text-4xl font-bold mb-4">About</h1>
            <p class="text-xl text-base-content/70 leading-relaxed">
                IndieWeb-native blogger. Writing about technology, life, and the absurdity of modern startup culture. 
                Own your content, own your domain.
            </p>
        </header>

        {{-- Content --}}
        <div class="prose prose-lg max-w-none space-y-8">
            
            <section>
                <h2 class="text-2xl font-bold mb-4">Why BlogWriter?</h2>
                <p class="text-base-content/80 leading-relaxed">
                    BlogWriter is an IndieWeb-native blogging platform that puts you in control of your content. 
                    No platform lock-in, no algorithms deciding who sees your work, no sudden policy changes 
                    that erase years of writing.
                </p>
                <p class="text-base-content/80 leading-relaxed mt-4">
                    "Remember when <em>we</em> owned the internet?" This platform is built on that philosophy—
                    your domain, your content, your rules.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4">Tech Stack</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="card bg-base-200">
                        <div class="card-body p-4">
                            <h3 class="font-bold">Laravel 12</h3>
                            <p class="text-sm text-base-content/60">PHP 8.4+</p>
                        </div>
                    </div>
                    <div class="card bg-base-200">
                        <div class="card-body p-4">
                            <h3 class="font-bold">SQLite</h3>
                            <p class="text-sm text-base-content/60">Serverless database</p>
                        </div>
                    </div>
                    <div class="card bg-base-200">
                        <div class="card-body p-4">
                            <h3 class="font-bold">Alpine.js</h3>
                            <p class="text-sm text-base-content/60">Reactive UI</p>
                        </div>
                    </div>
                    <div class="card bg-base-200">
                        <div class="card-body p-4">
                            <h3 class="font-bold">Tailwind CSS</h3>
                            <p class="text-sm text-base-content/60">v4 with DaisyUI</p>
                        </div>
                    </div>
                    <div class="card bg-base-200">
                        <div class="card-body p-4">
                            <h3 class="font-bold">Editor.js</h3>
                            <p class="text-sm text-base-content/60">Block editor</p>
                        </div>
                    </div>
                    <div class="card bg-base-200">
                        <div class="card-body p-4">
                            <h3 class="font-bold">IndieWeb</h3>
                            <p class="text-sm text-base-content/60">Microformats</p>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-2xl font-bold mb-4">Connect</h2>
                <div class="flex flex-wrap gap-3">
                    <a href="https://github.com/username" rel="me" class="btn btn-outline gap-2">
                        <i class="ph ph-github-logo text-xl"></i>
                        GitHub
                    </a>
                    <a href="https://twitter.com/username" rel="me" class="btn btn-outline gap-2">
                        <i class="ph ph-twitter-logo text-xl"></i>
                        Twitter
                    </a>
                    <a href="mailto:email@example.com" rel="me" class="btn btn-outline gap-2">
                        <i class="ph ph-envelope text-xl"></i>
                        Email
                    </a>
                </div>
            </section>

        </div>

        {{-- CTA --}}
        <div class="mt-12 p-6 bg-base-200 rounded-lg text-center">
            <h3 class="text-xl font-bold mb-2">Want to start your own blog?</h3>
            <p class="text-base-content/60 mb-4">BlogWriter is open source and free to use.</p>
            <a href="https://github.com/jacobodonnell/blogwriter" target="_blank" rel="noopener" class="btn btn-primary gap-2">
                <i class="ph ph-github-logo"></i>
                View on GitHub
            </a>
        </div>

    </div>

</x-layouts.public>
