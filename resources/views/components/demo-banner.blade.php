@if(config('demo.enabled'))
    @php
        $interval = (int) config('demo.reset_interval', 120);
        $cron = new Cron\CronExpression("*/{$interval} * * * *");
        $nextReset = $cron->getNextRunDate()->getTimestamp();
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
