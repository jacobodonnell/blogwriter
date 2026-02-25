<x-editor-modal x-ref="republishModal" title="Republish this article?">
    <p>This article was originally published on <strong x-text="originalPublishedAt"></strong>. The original
        publish date will be preserved.</p>

    <x-slot:actions>
        <button type="button" class="btn btn-success"
                @click="$refs.republishModal.close(); submitFullSave()">
            Republish
        </button>
    </x-slot:actions>
</x-editor-modal>
