export function addProductToCartFromProductDetail() {
    cy.get('[data-testid="pages-productdetail-addtocart-button"]').click();
}

export function addProductVariantToCartFromProductDetail(productCatnum:string) {
    const productVariantItemSelector =
        '[data-testid="pages-productdetail-variant-' +
        productCatnum +
        '"] ' +
        '[data-testid="blocks-product-addtocart"]';
    cy.get(productVariantItemSelector).click();
}
