<x-layouts.public :title="$category->name . ' - ' . config('app.name')">

    <x-slot:seo>
        <x-seo-meta :title="$category->name . ' - ' . config('app.name')" :description="$category->description" />
    </x-slot:seo>

    <x-category-layout
        :category="$category"
        :categories="$categories"
        :children="$children"
        :articles="$articles"
        :photos="$photos"
        :currentType="$currentType"
        :articleCount="$articleCount"
        :photoCount="$photoCount"
        :feedUrl="$category->permalink()"
        :searchPlaceholder="'Search in ' . $category->name . '...'"
    />

</x-layouts.public>
