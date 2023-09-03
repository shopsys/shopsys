import {
    loginFromHeader,
    checkUserIsLoggedIn,
    checkIfLoginLinkIsVisible,
    fillInEmailAndPasswordOnLoginPage,
} from './authenticationSupport';
import { customer1, DEFAULT_APP_STORE, url } from 'fixtures/demodata';
import { checkAndHideSuccessToast, checkUrl } from 'support';

describe('Authentication tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should login from header and then log out', () => {
        cy.visit('/');
        loginFromHeader(customer1.emailRegistered, customer1.password);
        checkUserIsLoggedIn();
        checkAndHideSuccessToast();
        cy.getByDataTestId('my-account-link').should('be.visible').realHover();
        cy.getByDataTestId('header-logout').should('be.visible').click();
        checkIfLoginLinkIsVisible();
    });

    it('should login from login page and then log out', () => {
        cy.visit(url.login);
        fillInEmailAndPasswordOnLoginPage(customer1.emailRegistered, customer1.password);
        cy.getByDataTestId('pages-login-submit').click();
        checkUserIsLoggedIn();
        checkAndHideSuccessToast();
        cy.getByDataTestId('my-account-link').click();
        checkUrl(url.customer);
        cy.getByDataTestId('customer-page-logout').click();
        checkUrl(url.loginWithCustomerRedirect);
        checkIfLoginLinkIsVisible();
    });
});
