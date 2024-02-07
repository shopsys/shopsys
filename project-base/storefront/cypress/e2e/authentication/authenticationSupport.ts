import { DataTestIds } from 'dataTestIds';
import { buttonName, link } from 'fixtures/demodata';

export const checkIfLoginLinkIsVisible = () => {
    cy.getByDataTestId([DataTestIds.layout_header_menuiconic_login_link_popup]).should('be.visible');
};

export const clickOnUserIconInHeader = () => {
    cy.getByDataTestId([DataTestIds.layout_header_menuiconic_login_link_popup]).click();
};

export const checkUserIsLoggedIn = () => {
    cy.getByDataTestId([DataTestIds.my_account_link]).contains(link.myAccount);
};

export const loginFromHeader = (email: string, password: string) => {
    clickOnUserIconInHeader();
    fillInEmailAndPasswordInLoginPopup(email, password);
    cy.getByDataTestId([DataTestIds.layout_popup]).get('button').contains(buttonName.login).click();
    checkUserIsLoggedIn();
};

export const fillInEmailAndPasswordInLoginPopup = (email: string, password: string) => {
    const getLoginPopup = () => cy.getByDataTestId([DataTestIds.layout_popup]);
    getLoginPopup().get('#login-form-email').type(email);
    getLoginPopup().get('#login-form-password').type(password);
};

export const fillInEmailAndPasswordOnLoginPage = (email: string, password: string) => {
    cy.get('#login-form-email').type(email);
    cy.get('#login-form-password').type(password);
};
