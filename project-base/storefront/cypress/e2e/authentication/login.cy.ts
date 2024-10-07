import {
    loginFromHeader,
    fillInEmailAndPasswordOnLoginPage,
    logoutFromHeader,
    submitLoginForm,
    logoutFromCustomerPage,
} from './authenticationSupport';
import { customer1, password, url } from 'fixtures/demodata';
import {
    checkAndHideSuccessToast,
    checkUrl,
    initializePersistStoreInLocalStorageToDefaultValues,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Login Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Login Page] login from login page and then log out', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.login);

        fillInEmailAndPasswordOnLoginPage(customer1.emailRegistered, password);
        submitLoginForm();
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after login', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider }, { tid: TIDs.simple_navigation_image }],
        });

        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.index);
        logoutFromCustomerPage();
        checkAndHideSuccessToast('Successfully logged out');
        checkUrl(url.loginWithCustomerRedirect);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after logout', { blackout: [{ tid: TIDs.footer_social_links }] });
    });

    it('[Header] login from header and then log out', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        loginFromHeader(customer1.emailRegistered, password);
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after login', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider }, { tid: TIDs.simple_navigation_image }],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after logout', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider }, { tid: TIDs.simple_navigation_image }],
        });
    });
});
