export const addProductToCartFromProductList = (productCatnum: string) => {
    cy.getByDataTestId(['blocks-product-list-listeditem-' + productCatnum, 'blocks-product-addtocart']).click();
};
