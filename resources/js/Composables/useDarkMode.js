import { ref } from 'vue';

const STORAGE_KEY = 'portfolio-theme';
const isDark = ref(false);
let initialized = false;

function applyTheme(dark) {
    if (typeof document === 'undefined') return;

    const root = document.documentElement;
    root.classList.toggle('dark', dark);
    root.style.colorScheme = dark ? 'dark' : 'light';
}

function persistTheme(dark) {
    try {
        localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
    } catch {
        // Theme still works when storage is unavailable (private mode, policy, etc.).
    }
}

function initialize() {
    if (typeof window === 'undefined') return;

    if (!initialized) {
        let stored = null;

        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch {
            // Fall back to the operating system preference.
        }

        isDark.value =
            stored === 'dark' ||
            (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        initialized = true;
    } else {
        // Keep the shared state aligned after Inertia history navigation.
        isDark.value = document.documentElement.classList.contains('dark');
    }

    applyTheme(isDark.value);
}

export function useDarkMode() {
    initialize();

    function setTheme(dark) {
        const nextTheme = Boolean(dark);

        // Apply synchronously during the user gesture. This avoids stale
        // component-scoped watchers after Inertia swaps the public layout.
        applyTheme(nextTheme);
        isDark.value = nextTheme;
        persistTheme(nextTheme);
    }

    function toggle() {
        const currentTheme =
            typeof document !== 'undefined'
                ? document.documentElement.classList.contains('dark')
                : isDark.value;

        setTheme(!currentTheme);
    }

    return { isDark, toggle, setTheme };
}
