export const GJS_PRODUCTS_SEPARATOR = '|||';
export const GJS_PRODUCT_SEPARATOR = ',';

export const parseCatnums = (text: string): string[] => {
    const dividedText = text.split(GJS_PRODUCTS_SEPARATOR).filter(Boolean);
    const productsRegex = new RegExp(/\[gjc-comp-ProductList&#61;(.+)]/g);
    const productCatnums: string[] = [];

    dividedText.forEach((textPart) => {
        const products = productsRegex.exec(textPart);
        if (products) {
            productCatnums.push(...products[1].split(GJS_PRODUCT_SEPARATOR));
        }
    });

    return productCatnums.filter((value, index) => productCatnums.indexOf(value) === index);
};
