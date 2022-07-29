export function addProductToCartFromProductList(productCatnum) {
    const productSelector =
        '[data-testid="blocks-product-list-listeditem-' +
        productCatnum +
        '"] ' +
        '[data-testid="blocks-product-addtocart"]';
    cy.get(productSelector).click();
}
