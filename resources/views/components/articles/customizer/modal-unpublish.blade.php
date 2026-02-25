<x-editor-modal x-ref="unpublishModal" title="Revert to draft?">
    <p>This article will no longer be visible on your site. Anyone with the link will see a 404 until you
        republish.</p>

    <x-slot:actions>
        <button type="button" class="btn btn-error"
                @click="$refs.unpublishModal.close(); submitFullSave()">
            Unpublish
        </button>
    </x-slot:actions>
</x-editor-modal>
