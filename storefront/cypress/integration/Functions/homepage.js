export function productClickFromPromotedProductsOnHomepage(productCatnum, productName) {
    const sliderProductItemSelector =
        '[data-testid="blocks-product-sliderproductitem-' +
        productCatnum +
        '"] ' +
        '[data-testid="blocks-product-sliderproductitem-name"]';
    cy.get(sliderProductItemSelector).contains(productName).click();
}

export function addProductToCartFromPromotedProductsOnHomepage(productCatnum) {
    const sliderProductItemSelector =
        '[data-testid="blocks-product-sliderproductitem-' +
        productCatnum +
        '"] ' +
        'button[data-testid="blocks-product-addtocart"]';
    cy.get(sliderProductItemSelector).click();
}
