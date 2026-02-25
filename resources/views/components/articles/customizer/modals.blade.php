@props(['categories'])

<x-articles.customizer.modal-publish />
<x-articles.customizer.modal-republish />
<x-articles.customizer.modal-unpublish />
<x-articles.customizer.modal-new-category :categories="$categories" />
<x-articles.customizer.modal-upload-photo />
