export default function sidebar() {
    return {
        expanded: localStorage.getItem('sidebarExpanded') !== 'false',
        mobileDrawerOpen: false,
        isDesktop: window.matchMedia('(min-width: 1024px)').matches,
        tooltipText: '',
        tooltipX: 0,
        tooltipY: 0,

        toggle() {
            if (this.isDesktop) {
                this.expanded = !this.expanded;
            } else {
                this.mobileDrawerOpen = !this.mobileDrawerOpen;
            }
        },

        closeMobile() {
            this.mobileDrawerOpen = false;
        },

        showTooltip(event, text) {
            if (this.expanded || !this.isDesktop) return;
            const rect = event.currentTarget.getBoundingClientRect();
            this.tooltipX = rect.right + 8;
            this.tooltipY = rect.top + (rect.height / 2) - 14;
            this.tooltipText = text;
        },

        hideTooltip() {
            this.tooltipText = '';
        },

        init() {
            const mql = window.matchMedia('(min-width: 1024px)');
            mql.addEventListener('change', (e) => {
                this.isDesktop = e.matches;
                if (!e.matches) {
                    this.mobileDrawerOpen = false;
                }
            });
            this.$watch('expanded', (v) => {
                localStorage.setItem('sidebarExpanded', v);
            });
        }
    };
}
