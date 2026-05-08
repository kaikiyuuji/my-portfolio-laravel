import { onMounted, onBeforeUnmount } from 'vue';

export function useScrollReveal(selector = '.reveal', options = {}) {
    let observer = null;

    onMounted(() => {
        if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
            document.querySelectorAll(selector).forEach((el) => el.classList.add('is-visible'));
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: options.threshold ?? 0.12,
                rootMargin: options.rootMargin ?? '0px 0px -40px 0px',
            },
        );

        document.querySelectorAll(selector).forEach((el) => observer.observe(el));
    });

    onBeforeUnmount(() => {
        if (observer) observer.disconnect();
    });
}
