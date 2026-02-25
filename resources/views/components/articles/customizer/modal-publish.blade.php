<x-editor-modal x-ref="publishModal" title="Publish this article?">
    <p>This article will be live and visible to everyone.</p>

    <x-slot:actions>
        <button type="button" class="btn btn-success"
                @click="$refs.publishModal.close(); submitFullSave()">
            Publish
        </button>
    </x-slot:actions>
</x-editor-modal>
