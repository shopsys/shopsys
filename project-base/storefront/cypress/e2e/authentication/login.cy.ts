import {
    fillInEmailAndPasswordOnLoginPage,
    loginFromHeader,
    logoutFromCustomerMenu,
    logoutFromHeader,
    submitLoginForm,
} from './authenticationSupport';
import { staticData, url } from 'fixtures/demodata';
import {
    checkAndHideSuccessToast,
    checkIsUserLoggedIn,
    checkIsUserLoggedOut,
    checkUrl,
    initializePersistStoreInLocalStorageToDefaultValues,
    translations,
} from 'support';

describe('Login Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Login Page] should login from login page and then log out', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.login);

        fillInEmailAndPasswordOnLoginPage(staticData.customer1.emailRegistered, staticData.user.password);
        submitLoginForm();
        checkAndHideSuccessToast(translations.toast.success.loggedIn);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedIn();

        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);
        logoutFromCustomerMenu();
        checkAndHideSuccessToast(translations.toast.success.loggedOut);
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
    });

    it('[Header] should login from header and then log out', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        loginFromHeader(staticData.customer1.emailRegistered, staticData.user.password);
        checkAndHideSuccessToast(translations.toast.success.loggedIn);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedIn();

        logoutFromHeader();
        checkAndHideSuccessToast(translations.toast.success.loggedOut);
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
    });
});
