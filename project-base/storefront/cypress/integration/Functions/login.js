import { buttonName } from '../../fixtures/demodata';
import { checkUserIsLoggedIn, clickOnUserIconInHeader } from './header';

export function loginFromHeader(email, password) {
    clickOnUserIconInHeader();
    fillInEmailAndPasswordInLoginPopup(email, password);
    cy.get('[data-testid="layout-popup"]').get('button').contains(buttonName.login).click();
    checkUserIsLoggedIn();
}

export function fillInEmailAndPasswordInLoginPopup(email, password) {
    cy.get('[data-testid="layout-popup"] #login-form-email').type(email);
    cy.get('[data-testid="layout-popup"] #login-formpassword').type(password);
}

export function fillInEmailAndPasswordOnLoginPage(email, password) {
    cy.get('#login-form-email').type(email);
    cy.get('#login-formpassword').type(password);
}
