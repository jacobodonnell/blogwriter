@if(config('demo.enabled'))
    <div class="bg-info text-info-content text-center py-1 text-sm">
        Demo mode — resets every 30 minutes. Login: {{ config('demo.credentials.email') }}
        / {{ config('demo.credentials.password') }}
    </div>
@endif
