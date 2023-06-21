export function checkProductAndGoToCartFromCartPopupWindow(productName) {
    cy.get('[data-testid="layout-popup"] [data-testid="blocks-product-addtocartpopup-product-name"]').contains(
        productName,
    );
    cy.get('[data-testid="layout-popup"] [data-testid="basic-link-button"]').click();
}
