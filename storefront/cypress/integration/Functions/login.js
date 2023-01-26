import { buttonName } from '../../fixtures/demodata';
import { checkSuccesfulLoginIconInHeader, clickOnUserIconInHeader } from './header';

export function succesfulLogInFromHeader(email, password) {
    clickOnUserIconInHeader();
    fillInEmailAndPasswordForLogin(email, password);
    cy.get('[data-testid="layout-popup"]').contains(buttonName.logIn).click();
    checkSuccesfulLoginIconInHeader();
}

export function fillInEmailAndPasswordForLogin(email, password) {
    cy.get('[data-testid="layout-popup"] #login-form-email').type(email);
    cy.get('[data-testid="layout-popup"] #login-formpassword').type(password);
}
