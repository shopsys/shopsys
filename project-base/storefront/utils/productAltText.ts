export const generateProductImageAlt = (productName: string, categoryName?: string): string => {
    return categoryName ? `${categoryName} - ${productName}` : productName;
};
