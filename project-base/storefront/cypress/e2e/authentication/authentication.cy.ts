import {
    loginFromHeader,
    checkUserIsLoggedIn,
    checkIfLoginLinkIsVisible,
    fillInEmailAndPasswordOnLoginPage,
} from './authenticationSupport';
import { customer1, DEFAULT_APP_STORE, url } from 'fixtures/demodata';
import { checkAndHideSuccessToast, checkUrl } from 'support';
import { TIDs } from 'tids';

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
        cy.getByTID([TIDs.my_account_link]).should('be.visible').realHover();
        cy.getByTID([TIDs.header_logout]).should('be.visible').click();
        checkIfLoginLinkIsVisible();
    });

    it('should login from login page and then log out', () => {
        cy.visit(url.login);
        fillInEmailAndPasswordOnLoginPage(customer1.emailRegistered, customer1.password);
        cy.getByTID([TIDs.pages_login_submit]).click();
        checkUserIsLoggedIn();
        checkAndHideSuccessToast();
        cy.getByTID([TIDs.my_account_link]).click();
        checkUrl(url.customer);
        cy.getByTID([TIDs.customer_page_logout]).click();
        checkUrl(url.loginWithCustomerRedirect);
        checkIfLoginLinkIsVisible();
    });
});
