import { getImageAlt } from 'utils/imageAltText';

export const generateProductImageAlt = (
    productName: string,
    categoryName?: string | null,
    imageName?: string | null,
): string => {
    return getImageAlt(imageName, categoryName ? `${categoryName} - ${productName}` : productName);
};
