export default function demoCountdown(targetTimestamp) {
    return {
        targetTimestamp,
        display: '',
        _interval: null,

        init() {
            this.tick();
            this._interval = setInterval(() => this.tick(), 1000);
        },

        destroy() {
            if (this._interval) {
                clearInterval(this._interval);
            }
        },

        tick() {
            const remaining = Math.max(0, this.targetTimestamp - Math.floor(Date.now() / 1000));

            if (remaining === 0) {
                this.display = 'resetting...';
                return;
            }

            this.display = this.format(remaining);
        },

        format(seconds) {
            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            if (days > 0) {
                return `${days}d ${hours}h`;
            }

            if (hours > 0) {
                return `${hours}h ${minutes}m`;
            }

            return `${minutes}m ${secs}s`;
        },
    };
}
