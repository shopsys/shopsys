import { logException } from 'utils/errors/logException';

const RECOMMENDER_PATHNAMES = {
    '/': 'homepage',
    '/blogArticles/[blogArticleSlug]': 'blog-article-detail',
    '/cart': 'cart',
    '/categories/[categorySlug]': 'category-detail',
    '/flags/[flagSlug]': 'flag-detail',
    '/product-comparison': 'product-comparison',
    '/products/[productSlug]': 'product-detail',
    '/search': 'search',
    '/wishlist': 'wishlist',
} as const;

export const getRecommenderClientIdentifier = (pathname: string): string => {
    const splitPathname = pathname.split('?')[0];
    if (!(splitPathname in RECOMMENDER_PATHNAMES)) {
        logException(`Pathname ${splitPathname} does not have a corresponding identifier in RECOMMENDER_PATHNAMES`);
        return splitPathname;
    }

    return RECOMMENDER_PATHNAMES[splitPathname as RecommenderClientIdentifierKeyType];
};

export type RecommenderClientIdentifierKeyType = keyof typeof RECOMMENDER_PATHNAMES;
