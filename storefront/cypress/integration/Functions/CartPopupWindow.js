export function checkProductAndGoToCartFromCartPopupWindow(productname) {
    cy.get('[data-testid="layout-popup"]');
    cy.get('[data-testid="blocks-product-addtocartpopup-product-name"]').contains(productname);
    cy.get('[data-testid="basic-link-button"]').click();
}
