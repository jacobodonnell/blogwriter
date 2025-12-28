<x-layout>
    <main class="mx-auto">
        <section class="hero bg-base-200 min-h-80">
            <div class="hero-content text-center">
                <div class="max-w-md">
                    <h1 class="text-5xl font-bold">Hello there</h1>
                    <p class="py-6">
                        Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem
                        quasi. In deleniti eaque aut repudiandae et a id nisi.
                    </p>
                    <button class="btn btn-primary">Get Started</button>
                </div>
            </div>
        </section>
        <section class="container mx-auto py-4 px-2">
            <h2 class="text-2xl font-[600]">Recent Articles</h2>
            @foreach($articles as $article)
                <div class="card bg-base-100 my-8 shadow-lg">
                    <div class="card-body">
                        <h2 class="card-title">Card title!</h2>
                        <p>{{ Str::limit($article, 280) }}</p>
                        <div class="card-actions justify-end">
                            <button class="btn btn-primary">Buy Now</button>
                        </div>
                    </div>
                </div>
            @endforeach

        </section>
    </main>
</x-layout>
