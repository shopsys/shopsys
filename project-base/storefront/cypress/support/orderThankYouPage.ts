import { link, placeholder, url } from 'fixtures/demodata';

export const checkFinishOrderPageAsUnregistredCustomer = () => {
    cy.url().should('contain', url.order.thanYouPage);
    cy.getByDataTestId('pages-orderconfirmation')
        .get('#registration-after-order-form-password')
        .should('have.attr', 'placeholder', placeholder.password);
};

export const checkFinishOrderPageAsUnloggedCustomerWithEmailWithExistingRegistration = () => {
    cy.url().should('contain', url.order.thanYouPage);
    cy.getByDataTestId('pages-orderconfirmation').get('#registration-after-order-form-password').should('not.exist');
};

export const clickOnOrderDetailButtonOnThankYouPage = () => {
    cy.getByDataTestId('pages-orderconfirmation').contains(link.orderDetail).click();
    cy.url().should('contain', url.order.detail);
};
