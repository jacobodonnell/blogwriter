{{-- AJAX toast (fired via $dispatch) --}}
<div x-data="{ show: false, message: '', type: 'success' }"
     @toast:show.window="message = $event.detail.message; type = $event.detail.type; show = true; setTimeout(() => show = false, 5000)"
     x-show="show"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     :class="type === 'success' ? 'alert-success' : 'alert-error'"
     class="fixed top-4 right-4 z-50 alert shadow-lg">
    <i :class="type === 'success' ? 'ph ph-check-circle' : 'ph ph-x-circle'" class="ph text-xl"></i>
    <span x-text="message"></span>
    <button @click="show = false" class="btn btn-ghost btn-xs" data-test="toast-dismiss">
        <i class="ph ph-x"></i>
    </button>
</div>

@if ($success)
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-4 right-4 z-50 alert alert-success shadow-lg"
         data-test="session-toast">
        <i class="ph ph-check-circle text-xl"></i>
        <span>{{ $success }}</span>
        <button @click="show = false" class="btn btn-ghost btn-xs" data-test="toast-dismiss">
            <i class="ph ph-x"></i>
        </button>
    </div>
@endif

@if ($error)
    <div x-data="{ show: true }"
         x-show="show"
         class="fixed top-4 right-4 z-50 alert alert-error shadow-lg"
         data-test="session-toast">
        <i class="ph ph-x-circle text-xl"></i>
        <span>{{ $error }}</span>
        <button @click="show = false" class="btn btn-ghost btn-xs" data-test="toast-dismiss">
            <i class="ph ph-x"></i>
        </button>
    </div>
@endif
