import { nextTick, onBeforeUnmount, onMounted } from 'vue';

export function useScrollReveal(selector = '.reveal', options = {}) {
    let observer = null;
    let fallbackTimer = null;
    let frame = null;
    let elements = [];

    function revealAll() {
        elements.forEach((element) => element.classList.add('is-visible'));
    }

    onMounted(async () => {
        await nextTick();

        frame = window.requestAnimationFrame(() => {
            elements = Array.from(document.querySelectorAll(selector));

            const isMobile = window.matchMedia('(max-width: 767px), (hover: none) and (pointer: coarse)').matches;
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // Mobile browsers, particularly Safari, can miss observer callbacks
            // after an Inertia transition. Content visibility wins over animation.
            if (isMobile || reduceMotion || !('IntersectionObserver' in window)) {
                revealAll();
                return;
            }

            observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add('is-visible');
                        observer?.unobserve(entry.target);
                    });
                },
                {
                    threshold: options.threshold ?? 0.08,
                    rootMargin: options.rootMargin ?? '0px 0px -24px 0px',
                },
            );

            elements.forEach((element) => observer.observe(element));

            // Safety net for browsers that create the observer but never deliver
            // callbacks after restoring or navigating a page.
            fallbackTimer = window.setTimeout(revealAll, options.fallbackDelay ?? 1400);
        });
    });

    onBeforeUnmount(() => {
        observer?.disconnect();
        window.cancelAnimationFrame(frame);
        window.clearTimeout(fallbackTimer);
    });
}
