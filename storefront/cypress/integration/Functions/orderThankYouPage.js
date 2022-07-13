import {
    order_detail_butoton,
    placeholder_password,
    url_order_detail,
    url_order_thank_you_page,
    url_order_thay_you_page,
} from '../../fixtures/demodata';

export function checkFinishOrderPageAsUnregistredCustomer() {
    cy.url().should('contain', url_order_thank_you_page);
    cy.get('[data-testid="pages-orderconfirmation"]');
    cy.get('[name="password"]').should('have.attr', 'placeholder', placeholder_password);
}

export function clickOnOrderDetailButtonOnThankYouPage() {
    cy.get('[data-testid="pages-orderconfirmation"]').contains(order_detail_butoton).click();
    cy.url().should('contain', url_order_detail);
}
