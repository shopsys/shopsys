import {
    loginFromHeader,
    fillInEmailAndPasswordOnLoginPage,
    logoutFromHeader,
    submitLoginFormOnLoginPage,
    logoutFromCustomerPage,
} from './authenticationSupport';
import { customer1, DEFAULT_APP_STORE, password, url } from 'fixtures/demodata';
import { checkAndHideSuccessToast, checkUrl, takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Login tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should login from login page and then log out', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.login);

        fillInEmailAndPasswordOnLoginPage(customer1.emailRegistered, password);
        submitLoginFormOnLoginPage();
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
        takeSnapshotAndCompare(this.test?.title, 'after logout');
    });

    it('should login from header and then log out', function () {
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
