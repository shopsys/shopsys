export const addProductToCartFromProductList = (productCatnum: string) => {
    const productSelector =
        '[data-testid="blocks-product-list-listeditem-' +
        productCatnum +
        '"] ' +
        '[data-testid="blocks-product-addtocart"]';
    cy.get(productSelector).click();
};
