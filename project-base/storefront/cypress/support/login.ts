import { buttonName } from 'fixtures/demodata';
import { checkUserIsLoggedIn, clickOnUserIconInHeader } from './header';

export const loginFromHeader = (email: string, password: string) => {
    clickOnUserIconInHeader();
    fillInEmailAndPasswordInLoginPopup(email, password);
    cy.get('[data-testid="layout-popup"]').get('button').contains(buttonName.login).click();
    checkUserIsLoggedIn();
};

export const fillInEmailAndPasswordInLoginPopup = (email: string, password: string) => {
    cy.get('[data-testid="layout-popup"] #login-form-email').type(email);
    cy.get('[data-testid="layout-popup"] #login-formpassword').type(password);
};

export const fillInEmailAndPasswordOnLoginPage = (email: string, password: string) => {
    cy.get('#login-form-email').type(email);
    cy.get('#login-formpassword').type(password);
};
