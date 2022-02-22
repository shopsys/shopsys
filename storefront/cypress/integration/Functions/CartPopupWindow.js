export function checkProductAndGoToCartFromCartPopupWindow(productname) {
    cy.get('[data-testid="layout-popup"]').contains('Skvělá volba! Vaše zboží jsme přidali do košíku');
    cy.get('[data-testid="blocks-product-addtocartpopup-product-name"]').contains(productname);
    cy.get('[data-testid="basic-link-button"]').contains('Do košíku').click();
}
