import { ref } from 'vue';

const STORAGE_KEY = 'portfolio-theme';
const isDark = ref(false);
let initialized = false;

function applyTheme(dark) {
    if (typeof document === 'undefined') return;
    document.documentElement.classList.toggle('dark', dark);
}

function persistTheme() {
    if (typeof window === 'undefined') return;
    try {
        localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light');
    } catch {
        // Theme toggling should still work when storage is blocked.
    }
}

function initialize() {
    if (typeof window === 'undefined') return;

    if (!initialized) {
        let stored = null;
        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch {
            // Fall back to the operating-system preference.
        }

        isDark.value = stored === 'dark'
            || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        initialized = true;
    }

    // PublicLayout can be remounted during Inertia navigation. Always reapply
    // the singleton state instead of relying on a component-scoped watcher.
    applyTheme(isDark.value);
}

export function useDarkMode() {
    initialize();

    function toggle() {
        isDark.value = !isDark.value;
        applyTheme(isDark.value);
        persistTheme();
    }

    return { isDark, toggle };
}
