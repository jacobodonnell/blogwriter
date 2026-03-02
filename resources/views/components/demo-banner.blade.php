@if(config('demo.enabled'))
    @php
        $cron = new Cron\CronExpression(App\Support\DemoSchedule::cronExpression());
        $nextReset = $cron->getNextRunDate()->getTimestamp();
    @endphp
    <div
        x-data="demoCountdown({{ $nextReset }})"
        class="bg-info text-info-content text-center py-1 text-sm"
    >
        <template x-if="!resetting">
            <span>
                Demo mode — resets in <span x-text="display"></span>.
                Login: {{ config('demo.credentials.email') }}
                / {{ config('demo.credentials.password') }}
            </span>
        </template>
        <template x-if="resetting">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-base-300/80">
                <div class="text-center">
                    <span class="loading loading-spinner loading-lg"></span>
                    <p class="mt-4 text-lg font-semibold">Demo is resetting...</p>
                </div>
            </div>
        </template>
    </div>
@endif
