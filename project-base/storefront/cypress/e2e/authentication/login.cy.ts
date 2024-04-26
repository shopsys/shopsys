import {
    loginFromHeader,
    checkUserIsLoggedIn,
    checkIfLoginLinkIsVisible,
    fillInEmailAndPasswordOnLoginPage,
    logoutFromHeader,
} from './authenticationSupport';
import { customer1, DEFAULT_APP_STORE, password, url } from 'fixtures/demodata';
import { checkAndHideSuccessToast, checkUrl } from 'support';
import { TIDs } from 'tids';

describe('Login tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should login from header and then log out', () => {
        cy.visitAndWaitForStableDOM('/');
        loginFromHeader(customer1.emailRegistered, password);
        checkAndHideSuccessToast();
        cy.waitForStableDOM({ pollInterval: 500, timeout: 5000 });
        checkUserIsLoggedIn();
        logoutFromHeader();
        cy.waitForStableDOM({ pollInterval: 500, timeout: 5000 });
        checkIfLoginLinkIsVisible();
    });

    it('should login from login page and then log out', () => {
        cy.visitAndWaitForStableDOM(url.login);
        fillInEmailAndPasswordOnLoginPage(customer1.emailRegistered, password);
        cy.getByTID([TIDs.pages_login_submit]).click();
        checkAndHideSuccessToast();
        cy.waitForStableDOM({ pollInterval: 500, timeout: 5000 });
        checkUserIsLoggedIn();
        cy.getByTID([TIDs.my_account_link]).click();
        checkUrl(url.customer.index);
        cy.getByTID([TIDs.customer_page_logout]).click();
        cy.waitForStableDOM({ pollInterval: 500, timeout: 5000 });
        checkUrl(url.loginWithCustomerRedirect);
        checkIfLoginLinkIsVisible();
    });
});
