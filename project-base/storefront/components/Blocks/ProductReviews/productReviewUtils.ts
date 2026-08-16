type ProductReviewsSummary = {
    averageRating: number | null;
    totalCount: number;
};

type ProductWithReviewsSummary = {
    __typename?: string;
    reviewsSummary?: ProductReviewsSummary | null;
    mainVariant?: object | null;
};

export const getProductReviewsSummary = (product: ProductWithReviewsSummary): ProductReviewsSummary | null => {
    if (
        product.__typename === 'Variant' &&
        product.mainVariant !== null &&
        product.mainVariant !== undefined &&
        'reviewsSummary' in product.mainVariant
    ) {
        return (product.mainVariant.reviewsSummary as ProductReviewsSummary | null | undefined) ?? null;
    }

    return product.reviewsSummary ?? null;
};

export const formatAverageRating = (averageRating: number, locale: string): string =>
    new Intl.NumberFormat(locale, { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(averageRating);

export const getReviewerInitial = (reviewerName: string | null): string | null =>
    reviewerName?.trim().charAt(0).toUpperCase() || null;

export const getProductReviewHtmlId = (productReviewUuid: string): string => `product-review-${productReviewUuid}`;
