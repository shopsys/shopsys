import { buttonName, link } from 'fixtures/demodata';

export const checkIfLoginLinkIsVisible = () => {
    cy.getByDataTestId('layout-header-menuiconic-login-link-popup').should('be.visible');
};

export const clickOnUserIconInHeader = () => {
    cy.getByDataTestId('layout-header-menuiconic-login-link-popup').click();
};

export const checkUserIsLoggedIn = () => {
    cy.getByDataTestId('my-account-link').contains(link.myAccount);
};

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
