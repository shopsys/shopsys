import { placeholder } from 'fixtures/demodata';

export function fillEmailInThirdStep(email: string) {
    cy.get('#contact-information-form-email').should('have.attr', 'placeholder', placeholder.email).type(email);
}

export function fillCustomerInformationInThirdStep(phone: string, firstName: string, lastName: string) {
    cy.get('#contact-information-form-telephone').should('have.attr', 'placeholder', placeholder.phone).type(phone);
    cy.get('#contact-information-form-firstName')
        .should('have.attr', 'placeholder', placeholder.firstName)
        .type(firstName);
    cy.get('#contact-information-form-lastName')
        .should('have.attr', 'placeholder', placeholder.lastName)
        .type(lastName);
}

export function fillBillingAdressInThirdStep(street: string, city: string, postCode: string) {
    cy.get('#contact-information-form-street').should('have.attr', 'placeholder', placeholder.street).type(street);
    cy.get('#contact-information-form-city').should('have.attr', 'placeholder', placeholder.city).type(city);
    cy.get('#contact-information-form-postcode')
        .should('have.attr', 'placeholder', placeholder.postCode)
        .type(postCode, { force: true });
}

export function clickOnSendOrderButton() {
    cy.get('[data-testid="blocks-orderaction-next"]').click();
}

export function fillInNoteInThirdStep(note: string) {
    cy.get('#contact-information-form-note').should('have.attr', 'placeholder', placeholder.note).type(note);
}
