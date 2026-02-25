@props(['article'])

<div x-init="
    $watch('$store.preview.mode', mode => {
        if (mode === 'live') {
            fetch('{{ $article->exists ? route('admin.articles.preview.live', $article) : '#' }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.text()).then(html => {
                const el = document.getElementById('preview-panel');
                if (el) el.outerHTML = html;
            });
        } else {
            document.querySelector('[x-ref=\'customizerForm\']')?.requestSubmit();
        }
    })
">
    @include('admin.articles.preview')
</div>
