const RECOMMENDER_PATHNAMES = {
    '/': 'homepage',
    '/products/[productSlug]': 'product-detail',
    '/cart': 'cart',
} as const;

export const getRecommenderClientIdentifier = (pathname: string): string => {
    const splitPathname = pathname.split('?')[0];
    if (!(splitPathname in RECOMMENDER_PATHNAMES)) {
        throw new Error(`Pathname ${splitPathname} does not have a corresponding recommender client identifier`);
    }

    return RECOMMENDER_PATHNAMES[splitPathname as RecommenderClientIdentifierKeyType];
};

export type RecommenderClientIdentifierKeyType = keyof typeof RECOMMENDER_PATHNAMES;
