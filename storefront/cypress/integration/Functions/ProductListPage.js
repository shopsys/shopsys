export function addProductToCartFromProductList(product_catnum) {
    const productSelector =
        '[data-testid="blocks-product-list-listeditem-' +
        product_catnum +
        '"] ' +
        '[data-testid="blocks-product-addtocart"]';
    cy.get(productSelector).contains('Do košíku').click();
}
