import { link, placeholder, url } from '../../fixtures/demodata';

export function checkFinishOrderPageAsUnregistredCustomer() {
    cy.url().should('contain', url.order.thanYouPage);
    cy.get('[data-testid="pages-orderconfirmation"]');
    cy.get('[name="password"]').should('have.attr', 'placeholder', placeholder.password);
}

export function clickOnOrderDetailButtonOnThankYouPage() {
    cy.get('[data-testid="pages-orderconfirmation"]').contains(link.orderDetail).click();
    cy.url().should('contain', url.order.detail);
}
