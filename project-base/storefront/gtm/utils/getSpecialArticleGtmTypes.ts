import { SPECIAL_ARTICLE_GTM_TYPES, type SpecialArticleGtmPageType } from 'gtm/types/objects';

/**
 * Determines if an article slug corresponds to a special GTM page type.
 *
 * @param slug - The article slug to check (e.g., '/about-us', '/o-nas')
 * @returns The corresponding GTM page type if found, otherwise null
 */
export const getSpecialArticleGtmType = (slug: string): SpecialArticleGtmPageType | null => {
    if (slug && typeof slug === 'string' && slug in SPECIAL_ARTICLE_GTM_TYPES) {
        return SPECIAL_ARTICLE_GTM_TYPES[slug as keyof typeof SPECIAL_ARTICLE_GTM_TYPES];
    }
    return null;
};
