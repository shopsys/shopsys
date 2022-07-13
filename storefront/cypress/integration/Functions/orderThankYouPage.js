import {
    placeholder_password,
    url_order_detail,
    url_order_thank_you_page,
    url_order_thay_you_page,
} from '../../fixtures/demodata';

export function checkFinishOrderPageAsUnregistredCustomer(customer_email) {
    cy.url().should('contain', url_order_thank_you_page);
    cy.get('[data-testid="pages-orderconfirmation"]').contains(customer_email);
    cy.get('[name="password"]').should('have.attr', 'placeholder', placeholder_password);
}

export function clickOnOrderDetailButton() {
    cy.get('[data-testid="basic-link-button"]').click();
    cy.url().should('contain', url_order_detail);
}
