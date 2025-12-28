<x-layout>
    <section class="container mx-auto px-2 py-4 [&>*+*]:my-2 [&>p]:text-base/10">
        <h1 class="text-4xl font-[600]">{{ $article->title }}</h1>
        {!! ($article->content) !!}
    </section>
</x-layout>
