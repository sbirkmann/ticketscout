import { reactive, watch } from 'vue';

const isBrowser = typeof window !== 'undefined';
const storedTheme = isBrowser ? localStorage.getItem('theme') : 'light';

export const themeStore = reactive({
    isDark: storedTheme === 'dark' || (!storedTheme && isBrowser && window.matchMedia('(prefers-color-scheme: dark)').matches),

    toggle() {
        this.isDark = !this.isDark;
    },

    init() {
        if (!isBrowser) return;
        this.applyTheme();
        
        watch(() => this.isDark, () => {
            this.applyTheme();
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        });
    },

    applyTheme() {
        if (!isBrowser) return;
        if (this.isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
});
