export type ReviewedProductVariantType = {
    uuid: string;
    fullName: string;
};

export type CreateProductReviewPopupProps = {
    productUuid: string | null;
    productName: string;
    variants?: ReviewedProductVariantType[];
    orderUrlHash?: string;
    guestPrefill?: {
        firstName: string;
        lastName: string;
        email: string;
    };
};

export type ProductReviewDisplayStatus = 'awaitingApproval' | 'verifiedPurchase';
