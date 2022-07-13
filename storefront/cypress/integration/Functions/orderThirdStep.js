import {
    placeholder_city,
    placeholder_email,
    placeholder_first_name,
    placeholder_last_name,
    placeholder_phone,
    placeholder_street,
    placeholder_zip,
} from '../../fixtures/demodata';

export function fillEmailInThirdStep(email) {
    cy.get('#contact-information-form-email').should('have.attr', 'placeholder', placeholder_email).type(email);
}

export function fillCustomerInformationInThirdStep(phone, first_name, last_name) {
    cy.get('#contact-information-form-telephone').should('have.attr', 'placeholder', placeholder_phone).type(phone);
    cy.get('#contact-information-form-firstName')
        .should('have.attr', 'placeholder', placeholder_first_name)
        .type(first_name);
    cy.get('#contact-information-form-lastName')
        .should('have.attr', 'placeholder', placeholder_last_name)
        .type(last_name);
}

export function fillBillingAdressInThirdStep(street, city, zip_code) {
    cy.get('#contact-information-form-street').should('have.attr', 'placeholder', placeholder_street).type(street);
    cy.get('#contact-information-form-city').should('have.attr', 'placeholder', placeholder_city).type(city);
    cy.get('#contact-information-form-postcode').should('have.attr', 'placeholder', placeholder_zip).type(zip_code);
}

export function clickOnSendOrderButton() {
    cy.get('[data-testid="blocks-orderaction-next"]').click();
}
