import { placeholder_password, url_order_thank_you_page, url_order_thay_you_page } from '../../fixtures/demodata';

export function checkFinishOrderPageAsUnregistredCustomer(customer_email) {
    cy.url().should('contain', url_order_thank_you_page);
    cy.get('[data-testid="pages-orderconfirmation"]').contains(customer_email);
    cy.get('[data-testid="layout-webline"][name="password"]').should('have.attr', 'placeholder', placeholder_password);
}
