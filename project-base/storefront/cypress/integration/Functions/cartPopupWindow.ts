export function checkProductAndGoToCartFromCartPopupWindow(productName: string) {
    cy.get('[data-testid="layout-popup"] [data-testid="blocks-product-addtocartpopup-product-name"]').contains(
        productName,
    );
    cy.get('[data-testid="layout-popup"] [data-testid="basic-link-button"]').click();
}
