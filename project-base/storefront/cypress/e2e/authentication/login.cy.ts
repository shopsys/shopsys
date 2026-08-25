import {
    checkHeaderLoginFormValues,
    closeHeaderLoginForm,
    fillInEmailAndPasswordOnLoginPage,
    fillInHeaderLoginForm,
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
import { TIDs } from 'tids';

const checkRefreshCookieIsProtected = () => {
    cy.getCookie('refreshToken-1').should((cookie) => {
        expect(cookie).not.to.be.null;
        expect(cookie?.httpOnly).to.be.true;
        expect(cookie?.sameSite).to.equal('lax');
        expect(cookie?.secure).to.equal(Cypress.config('baseUrl')?.startsWith('https') ?? false);
    });
    cy.window()
        .its('document.cookie')
        .should('not.contain', 'refreshToken-1=')
        .and('contain', 'refreshTokenPresent-1=1');
};

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
        checkRefreshCookieIsProtected();

        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);
        cy.intercept('POST', '/api/auth/token', (request) => {
            if (request.body.operationName === 'RefreshTokens') {
                expect(request.body.variables.refreshToken).to.equal('');
                request.alias = 'refreshTokens';
            }
        });
        cy.clearCookie('accessToken-1');
        logoutFromCustomerMenu();
        cy.wait('@refreshTokens');
        checkAndHideSuccessToast(translations.toast.success.loggedOut);
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        cy.getCookie('accessToken-1').should('be.null');
        cy.getCookie('refreshToken-1').should('be.null');
        cy.getCookie('refreshTokenPresent-1').should('be.null');
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

    it('[Header Email Draft] should preserve email in the login flyout until navigating to another page', () => {
        const emailDraft = 'unfinished-login@shopsys.com';

        cy.visitAndWaitForStableAndInteractiveDOM('/');

        fillInHeaderLoginForm(emailDraft, staticData.user.password);
        closeHeaderLoginForm();
        checkHeaderLoginFormValues(emailDraft, '');

        closeHeaderLoginForm();
        cy.getByTID([TIDs.header, TIDs.header_stores_link]).should('be.visible').click();
        checkUrl(url.stores);
        cy.waitForStableAndInteractiveDOM();

        checkHeaderLoginFormValues('', '');
    });
});
