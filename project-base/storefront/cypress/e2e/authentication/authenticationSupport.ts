import { buttonName, link } from 'fixtures/demodata';
import { TIDs } from 'tids';

export const checkIfLoginLinkIsVisible = () => {
    cy.getByTID([TIDs.layout_header_menuiconic_login_link_popup]).should('be.visible');
};

export const clickOnUserIconInHeader = () => {
    cy.getByTID([TIDs.layout_header_menuiconic_login_link_popup]).click();
};

export const checkUserIsLoggedIn = () => {
    cy.getByTID([TIDs.my_account_link]).contains(link.myAccount);
};

export const loginFromHeader = (email: string, password: string) => {
    clickOnUserIconInHeader();
    fillInEmailAndPasswordInLoginPopup(email, password);
    cy.getByTID([TIDs.layout_popup]).get('button').contains(buttonName.login).click();
    checkUserIsLoggedIn();
};

export const fillInEmailAndPasswordInLoginPopup = (email: string, password: string) => {
    const getLoginPopup = () => cy.getByTID([TIDs.layout_popup]);
    getLoginPopup().get('#login-form-email').type(email);
    getLoginPopup().get('#login-form-password').type(password);
};

export const fillInEmailAndPasswordOnLoginPage = (email: string, password: string) => {
    cy.get('#login-form-email').type(email);
    cy.get('#login-form-password').type(password);
};
