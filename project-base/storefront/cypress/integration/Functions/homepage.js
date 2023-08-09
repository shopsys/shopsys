export function productClickFromPromotedProductsOnHomepage(productCatnum, productName) {
    const sliderProductItemSelector = `
    [data-testid="blocks-product-slider-promoted-products"] 
    [data-testid="blocks-product-list-listeditem-${productCatnum}"] 
    [data-testid="basic-heading-h3"]
    `;

    cy.get(sliderProductItemSelector).contains(productName).click();
}

export function addProductToCartFromPromotedProductsOnHomepage(productCatnum) {
    const sliderProductItemSelector = `
    [data-testid="blocks-product-slider-promoted-products"] 
    [data-testid="blocks-product-list-listeditem-${productCatnum}"] 
    button[data-testid="blocks-product-addtocart"]
    `;

    cy.get(sliderProductItemSelector).click();
}
