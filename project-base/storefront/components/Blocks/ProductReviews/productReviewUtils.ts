export const formatAverageRating = (averageRating: number, locale: string): string =>
    new Intl.NumberFormat(locale, { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(averageRating);

export const getReviewerInitial = (reviewerName: string | null): string | null =>
    reviewerName?.trim().charAt(0).toUpperCase() || null;

export const getProductReviewHtmlId = (productReviewUuid: string): string => `product-review-${productReviewUuid}`;
