import { link, placeholder, url } from 'fixtures/demodata';
import { TIDs } from 'tids';

export const fillEmailInThirdStep = (email: string) => {
    cy.get('#contact-information-form-email').should('have.attr', 'placeholder', placeholder.email).type(email);
};

export const fillCustomerInformationInThirdStep = (phone: string, firstName: string, lastName: string) => {
    cy.get('#contact-information-form-telephone').should('have.attr', 'placeholder', placeholder.phone).type(phone);
    cy.get('#contact-information-form-firstName')
        .should('have.attr', 'placeholder', placeholder.firstName)
        .type(firstName);
    cy.get('#contact-information-form-lastName')
        .should('have.attr', 'placeholder', placeholder.lastName)
        .type(lastName);
};

export const fillBillingAdressInThirdStep = (street: string, city: string, postCode: string) => {
    cy.get('#contact-information-form-street').should('have.attr', 'placeholder', placeholder.street).type(street);
    cy.get('#contact-information-form-city').should('have.attr', 'placeholder', placeholder.city).type(city);
    cy.get('#contact-information-form-postcode')
        .should('have.attr', 'placeholder', placeholder.postCode)
        .type(postCode, { force: true });
};

export const clickOnSendOrderButton = () => {
    cy.getByTID([TIDs.blocks_orderaction_next]).click();
};

export const fillInNoteInThirdStep = (note: string) => {
    cy.get('#contact-information-form-note').should('have.attr', 'placeholder', placeholder.note).type(note);
};

export const checkFinishOrderPageAsUnregistredCustomer = () => {
    cy.url().should('contain', url.order.orderConfirmation);
    cy.getByTID([TIDs.pages_orderconfirmation])
        .get('#registration-after-order-form-password')
        .should('have.attr', 'placeholder', placeholder.password);
};

export const checkFinishOrderPageAsUnloggedCustomerWithEmailWithExistingRegistration = () => {
    cy.url().should('contain', url.order.orderConfirmation);
    cy.getByTID([TIDs.pages_orderconfirmation]).get('#registration-after-order-form-password').should('not.exist');
};

export const clickOnOrderDetailButtonOnThankYouPage = () => {
    cy.getByTID([TIDs.pages_orderconfirmation]).contains(link.orderDetail).click();
    cy.url().should('contain', url.order.orderDetail);
};
