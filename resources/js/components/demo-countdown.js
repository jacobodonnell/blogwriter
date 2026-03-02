export default function demoCountdown(targetTimestamp) {
    return {
        targetTimestamp,
        display: '',
        resetting: false,
        _interval: null,
        _pollInterval: null,

        init() {
            this.tick();
            this._interval = setInterval(() => this.tick(), 1000);
        },

        destroy() {
            if (this._interval) {
                clearInterval(this._interval);
            }
            if (this._pollInterval) {
                clearInterval(this._pollInterval);
            }
        },

        tick() {
            const remaining = Math.max(0, this.targetTimestamp - Math.floor(Date.now() / 1000));

            if (remaining === 0) {
                this.display = 'resetting...';
                this.startResetting();
                return;
            }

            this.display = this.format(remaining);
        },

        startResetting() {
            if (this.resetting) {
                return;
            }

            this.resetting = true;
            clearInterval(this._interval);
            this._interval = null;

            this._pollInterval = setInterval(() => this.pollHealth(), 3000);
        },

        pollHealth() {
            fetch('/up', { redirect: 'manual' })
                .then((response) => {
                    if (response.ok) {
                        clearInterval(this._pollInterval);
                        this._pollInterval = null;
                        window.location.reload();
                    }
                })
                .catch(() => {
                    // Server not ready yet, keep polling
                });
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
