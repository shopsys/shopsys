export const productClickFromPromotedProductsOnHomepage = (productCatnum: string, productName: string) => {
    cy.getByDataTestId([
        'blocks-product-slider-promoted-products',
        'blocks-product-list-listeditem-' + productCatnum + '-name',
    ])
        .contains(productName)
        .click();
};

export const addProductToCartFromPromotedProductsOnHomepage = (productCatnum: string) => {
    cy.getByDataTestId([
        'blocks-product-slider-promoted-products',
        'blocks-product-list-listeditem-' + productCatnum,
        'blocks-product-addtocart',
    ]).click();
};
