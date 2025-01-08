import {
    fillInEmailAndPasswordOnLoginPage,
    loginFromHeader,
    logoutFromCustomerPage,
    logoutFromHeader,
    submitLoginForm,
} from './authenticationSupport';
import { customer1, password, url } from 'fixtures/demodata';
import {
    checkAndHideSuccessToast,
    checkIsUserLoggedIn,
    checkIsUserLoggedOut,
    checkUrl,
    initializePersistStoreInLocalStorageToDefaultValues,
} from 'support';

describe('Login Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Login Page] should login from login page and then log out', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.login);

        fillInEmailAndPasswordOnLoginPage(customer1.emailRegistered, password);
        submitLoginForm();
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedIn();

        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);
        logoutFromCustomerPage();
        checkAndHideSuccessToast('Successfully logged out');
        checkUrl(url.loginWithCustomerRedirect);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
    });

    it('[Header] should login from header and then log out', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        loginFromHeader(customer1.emailRegistered, password);
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedIn();

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
    });
});
