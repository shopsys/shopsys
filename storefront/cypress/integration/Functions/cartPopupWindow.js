export function checkProductAndGoToCartFromCartPopupWindow(productName) {
    cy.get('[data-testid="layout-popup"]');
    cy.get('[data-testid="blocks-product-addtocartpopup-product-name"]').contains(productName);
    cy.get('[data-testid="basic-link-button"]').click();
}
