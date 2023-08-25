import { buttonName } from 'fixtures/demodata';
import { checkUserIsLoggedIn, clickOnUserIconInHeader } from './header';

export const loginFromHeader = (email: string, password: string) => {
    clickOnUserIconInHeader();
    fillInEmailAndPasswordInLoginPopup(email, password);
    cy.getByDataTestId('layout-popup').get('button').contains(buttonName.login).click();
    checkUserIsLoggedIn();
};

export const fillInEmailAndPasswordInLoginPopup = (email: string, password: string) => {
    const getLoginPopup = () => cy.getByDataTestId('layout-popup');
    getLoginPopup().get('#login-form-email').type(email);
    getLoginPopup().get('#login-form-password').type(password);
};

export const fillInEmailAndPasswordOnLoginPage = (email: string, password: string) => {
    cy.get('#login-form-email').type(email);
    cy.get('#login-form-password').type(password);
};
