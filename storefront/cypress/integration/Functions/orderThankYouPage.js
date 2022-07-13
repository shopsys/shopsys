import { linkToOrderDetail, placeholder, urlOrderDetail, urlOrderThankYouPage } from '../../fixtures/demodata';

export function checkFinishOrderPageAsUnregistredCustomer() {
    cy.url().should('contain', urlOrderThankYouPage);
    cy.get('[data-testid="pages-orderconfirmation"]');
    cy.get('[name="password"]').should('have.attr', 'placeholder', placeholder.password);
}

export function clickOnOrderDetailButtonOnThankYouPage() {
    cy.get('[data-testid="pages-orderconfirmation"]').contains(linkToOrderDetail).click();
    cy.url().should('contain', urlOrderDetail);
}
