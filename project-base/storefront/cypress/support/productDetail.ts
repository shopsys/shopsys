export const addProductToCartFromProductDetail = () => {
    cy.getByDataTestId('pages-productdetail-addtocart-button').click();
};

export const addProductVariantToCartFromProductDetail = (productCatnum: string) => {
    cy.getByDataTestId(['pages-productdetail-variant-' + productCatnum, 'blocks-product-addtocart']).click();
};
