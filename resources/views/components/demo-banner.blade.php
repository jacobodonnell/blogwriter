@if(config('demo.enabled'))
    @php
        $lockFile = storage_path('installed.lock');
        $intervalSeconds = (int) config('demo.reset_interval', 30) * 60;
        $lastReset = file_exists($lockFile) ? filemtime($lockFile) : time();
        $nextReset = $lastReset + $intervalSeconds;
    @endphp
    <div
        x-data="demoCountdown({{ $nextReset }})"
        class="bg-info text-info-content text-center py-1 text-sm"
    >
        Demo mode — resets in <span x-text="display"></span>.
        Login: {{ config('demo.credentials.email') }}
        / {{ config('demo.credentials.password') }}
    </div>
@endif
