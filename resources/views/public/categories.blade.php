<x-layouts.public :title="'Categories - ' . config('app.name')">

    <x-slot:seo>
        <x-seo-meta :title="'Categories - ' . config('app.name')" description="Browse all categories" />
    </x-slot:seo>

    <x-category-layout
        :category="null"
        :categories="$categories"
        :children="$children"
        :articles="$articles"
        :photos="$photos"
        :currentType="$currentType"
        :articleCount="$articleCount"
        :photoCount="$photoCount"
        :feedUrl="route('categories.index')"
        searchPlaceholder="Search all content..."
    />

</x-layouts.public>
