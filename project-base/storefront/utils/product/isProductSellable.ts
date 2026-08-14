type ProductSellability = {
    isVisible?: boolean;
    isMainVariant?: boolean;
    isSellingDenied: boolean;
    isCurrentlyOutOfStock: boolean;
    isInquiryType: boolean;
};

export const isProductSellable = ({
    isVisible = true,
    isMainVariant = false,
    isSellingDenied,
    isCurrentlyOutOfStock,
    isInquiryType,
}: ProductSellability): boolean =>
    isVisible && !isMainVariant && !isSellingDenied && !isCurrentlyOutOfStock && !isInquiryType;
