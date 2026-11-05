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
import { PERSIST_STORE_NAME, staticData, url } from 'fixtures/demodata';
import {
    checkAndHideErrorToast,
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

    it('[Login Page] should login from login page and then log out', () => {
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

        cy.visitAndWaitForStableAndInteractiveDOM(url.login);
        cy.getByTID([TIDs.login_method_web, TIDs.last_used_login_method_badge])
            .should('be.visible')
            .and('contain.text', translations.login.lastUsed);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        cy.getByTID([TIDs.login_method_web, TIDs.last_used_login_method_badge]).should('be.visible');
    });

    it('[Header] should login from header and then log out', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        loginFromHeader(staticData.customer1.emailRegistered, staticData.user.password);
        checkAndHideSuccessToast(translations.toast.success.loggedIn);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedIn();

        cy.getByTID([TIDs.header, TIDs.my_account_link]).realHover().should('have.attr', 'aria-expanded', 'false');
        cy.getByTID([TIDs.header, TIDs.my_account_link]).click().should('have.attr', 'aria-expanded', 'true');
        cy.getByTID([TIDs.overlay]).should('be.visible').realHover({ position: 'topLeft' });
        cy.getByTID([TIDs.header, TIDs.user_menu_my_orders_link]).should('be.visible');
        cy.realPress('Escape');
        cy.getByTID([TIDs.header, TIDs.my_account_link]).should('have.attr', 'aria-expanded', 'false').and('be.focused');
        cy.getByTID([TIDs.overlay]).should('not.exist');
        cy.getByTID([TIDs.header, TIDs.my_account_link]).click();
        cy.getByTID([TIDs.overlay]).click('topLeft');
        cy.getByTID([TIDs.header, TIDs.my_account_link]).should('have.attr', 'aria-expanded', 'false');

        logoutFromHeader();
        checkAndHideSuccessToast(translations.toast.success.loggedOut);
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
    });

    it('[Header Dialog] should stay open after leaving the trigger and after a failed login', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.getByTID([TIDs.header, TIDs.my_account_link])
            .realHover()
            .should('have.attr', 'aria-expanded', 'false');
        cy.getByTID([TIDs.layout_popup]).should('not.exist');

        fillInHeaderLoginForm(staticData.customer1.emailRegistered, 'invalid-password');
        cy.getByTID([TIDs.overlay]).realHover({ position: 'topLeft' });
        cy.getByTID([TIDs.layout_popup]).should('have.length', 1).and('be.visible');
        submitLoginForm();
        checkAndHideErrorToast(translations.toast.error.invalidCredentials);
        cy.getByTID([TIDs.layout_popup, TIDs.login_form]).should('be.visible');
        cy.viewport(320, 568);
        cy.getByTID([TIDs.layout_popup]).children().last().should(($content) => {
            expect($content[0].scrollWidth).to.equal($content[0].clientWidth);
        });
        cy.getByTID([TIDs.layout_popup, TIDs.login_popup_register_link]).scrollIntoView().should('be.visible');
        cy.getByTID([TIDs.layout_popup, TIDs.login_popup_register_button]).should('not.be.visible');
        cy.getByTID([TIDs.layout_popup]).find('button').first().focus();
        cy.realPress(['Shift', 'Tab']);
        cy.getByTID([TIDs.layout_popup, TIDs.login_popup_register_link]).should('be.focused');
        cy.realPress('Tab');
        cy.getByTID([TIDs.layout_popup]).find('button').first().should('be.focused');
        cy.viewport(1600, 720);
        closeHeaderLoginForm();
        cy.getByTID([TIDs.header, TIDs.my_account_link]).should('be.focused');

        cy.realPress('Enter');
        cy.getByTID([TIDs.layout_popup]).should('be.visible');
        cy.focused().should(($focused) => {
            expect($focused.closest('[role="dialog"]')).to.have.length(1);
        });
        cy.getByTID([TIDs.layout_popup, TIDs.login_popup_register_button]).focus();
        cy.realPress('Tab');
        cy.getByTID([TIDs.layout_popup]).find('button').first().should('be.focused');
        cy.realPress(['Shift', 'Tab']);
        cy.getByTID([TIDs.layout_popup, TIDs.login_popup_register_button]).should('be.focused');
        closeHeaderLoginForm();
    });

    it('[Header Email Draft] should preserve email in the login dialog until navigating to another page', () => {
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

    it('[Last Login Method] should keep the previous method after an unsuccessful email login', () => {
        const previousLoginType = 'google';

        cy.window().then((win) => {
            const persistStore = JSON.parse(win.localStorage.getItem(PERSIST_STORE_NAME)!);
            persistStore.state.lastLoginType = previousLoginType;
            win.localStorage.setItem(PERSIST_STORE_NAME, JSON.stringify(persistStore));
        });
        cy.visitAndWaitForStableAndInteractiveDOM(url.login);

        cy.getByTID([
            [TIDs.social_network_login_link_, previousLoginType],
            TIDs.last_used_login_method_badge,
        ])
            .should('be.visible')
            .and('contain.text', translations.login.lastUsed);
        cy.getByTID([[TIDs.social_network_login_link_, previousLoginType]])
            .should('have.attr', 'aria-label')
            .and('contain', translations.login.lastUsed);
        cy.getByTID([TIDs.last_used_login_method_badge]).should('have.length', 1);
        fillInEmailAndPasswordOnLoginPage(staticData.customer1.emailRegistered, 'invalid-password');
        submitLoginForm();

        cy.getByTID([
            [TIDs.social_network_login_link_, previousLoginType],
            TIDs.last_used_login_method_badge,
        ]).should('be.visible');
        cy.window().then((win) => {
            const persistStore = JSON.parse(win.localStorage.getItem(PERSIST_STORE_NAME)!);
            expect(persistStore.state.lastLoginType).to.equal(previousLoginType);
        });
    });
});
