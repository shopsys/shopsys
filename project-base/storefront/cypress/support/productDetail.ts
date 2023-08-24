export const addProductToCartFromProductDetail = () => {
    cy.get('[data-testid="pages-productdetail-addtocart-button"]').click();
};

export const addProductVariantToCartFromProductDetail = (productCatnum: string) => {
    const productVariantItemSelector =
        '[data-testid="pages-productdetail-variant-' +
        productCatnum +
        '"] ' +
        '[data-testid="blocks-product-addtocart"]';
    cy.get(productVariantItemSelector).click();
};
