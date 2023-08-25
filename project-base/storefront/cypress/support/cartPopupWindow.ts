export const checkProductAndGoToCartFromCartPopupWindow = (productName: string) => {
    cy.getByDataTestId(['layout-popup', 'blocks-product-addtocartpopup-product-name']).contains(productName);
    cy.getByDataTestId(['layout-popup', 'basic-link-button']).click();
};
