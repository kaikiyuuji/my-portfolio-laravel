import { nextTick, onMounted, onBeforeUnmount } from 'vue';

export function useScrollReveal(selector = '.reveal', options = {}) {
    let observer = null;
    let fallbackTimer = null;
    let frame = null;
    let elements = [];

    const reveal = (element) => {
        element.classList.remove('reveal-pending');
        element.classList.add('is-visible');
        observer?.unobserve(element);
    };

    const revealVisible = () => {
        const margin = options.preloadMargin ?? 160;

        elements.forEach((element) => {
            if (element.classList.contains('is-visible')) return;

            const rect = element.getBoundingClientRect();
            if (rect.top <= window.innerHeight + margin && rect.bottom >= -margin) {
                reveal(element);
            }
        });
    };

    onMounted(async () => {
        await nextTick();

        if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
            document.querySelectorAll(selector).forEach((el) => el.classList.add('is-visible'));
            return;
        }

        elements = [...document.querySelectorAll(selector)];

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            elements.forEach(reveal);
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        reveal(entry.target);
                    }
                });
            },
            {
                threshold: options.threshold ?? 0.01,
                rootMargin: options.rootMargin ?? '160px 0px 160px 0px',
            },
        );

        elements.forEach((element) => {
            element.classList.add('reveal-pending');
            observer.observe(element);
        });

        frame = requestAnimationFrame(revealVisible);
        window.addEventListener('pageshow', revealVisible);
        window.addEventListener('resize', revealVisible, { passive: true });
        document.addEventListener('visibilitychange', revealVisible);

        // Safety net for mobile browsers that occasionally stop delivering
        // IntersectionObserver callbacks after theme or history changes.
        fallbackTimer = window.setTimeout(() => {
            elements.forEach(reveal);
        }, options.fallbackDelay ?? 2200);
    });

    onBeforeUnmount(() => {
        if (observer) observer.disconnect();
        if (fallbackTimer) window.clearTimeout(fallbackTimer);
        if (frame) cancelAnimationFrame(frame);

        window.removeEventListener('pageshow', revealVisible);
        window.removeEventListener('resize', revealVisible);
        document.removeEventListener('visibilitychange', revealVisible);
    });
}
