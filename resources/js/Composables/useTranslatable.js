import { useI18n } from 'vue-i18n';

/**
 * Resolve translatable fields stored as `{ pt: '...', en: '...' }`
 * (spatie/laravel-translatable) against the current i18n locale, with fallbacks
 * to pt and en.
 *
 * Usage in <script setup>:
 *   const { tr } = useTranslatable();
 *   tr(post.title);
 */
export function useTranslatable() {
    const { locale } = useI18n();

    const tr = (field) => {
        if (field == null) return '';
        if (typeof field === 'object') {
            return field[locale.value] || field.pt || field.en || '';
        }
        return field;
    };

    return { tr };
}
