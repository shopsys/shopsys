import { useSeoPage } from 'utils/seo/useSeoPage';

/**
 * Static pages (cart, wishlist, ...) have their H1 hardcoded in the storefront, this hook lets the SEO page
 * configured in the administration override it. The SEO page query is already fired by SeoMeta, so this
 * is served from the urql cache without an additional request.
 */
export const useSeoPageH1 = (defaultH1: string): string => {
    const seoPage = useSeoPage();

    return seoPage?.seo.h1 || defaultH1;
};
