import {
    goToRegistrationPageFromHeader,
    fillInRegstrationForm,
    checkRegistrationValidationErrorsPopup,
    submitRegistrationForm,
    clearAndFillInRegstrationFormEmail,
    clearAndFillInRegistrationFormPasswords,
} from './authenticationSupport';
import { DEFAULT_APP_STORE, password, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkAndHideErrorToast,
    checkAndHideSuccessToast,
    checkPopupIsVisible,
    checkUrl,
    goToEditProfileFromHeader,
    loseFocus,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Registration tests (basic)', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('should register as a B2C customer', function () {
        goToRegistrationPageFromHeader();
        const email = 'register-as-b2c@shopsys.com';
        clearAndFillInRegstrationFormEmail(email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(password);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled registration form');

        submitRegistrationForm();
        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();

        goToEditProfileFromHeader();
        checkUrl(url.customer.editProfile);
        takeSnapshotAndCompare(this.test?.title, 'customer edit page');
    });

    it('should register as a B2B customer', function () {
        goToRegistrationPageFromHeader();
        const email = 'register-as-b2b@shopsys.com';
        clearAndFillInRegstrationFormEmail(email);
        fillInRegstrationForm('companyCustomer', email);
        clearAndFillInRegistrationFormPasswords(password);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled registration form');

        submitRegistrationForm();
        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();

        goToEditProfileFromHeader();
        checkUrl(url.customer.editProfile);
        takeSnapshotAndCompare(this.test?.title, 'customer edit page');
    });
});

describe('Registration tests (repeated tries)', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
        cy.visitAndWaitForStableAndInteractiveDOM(url.registration);
    });

    it('should disallow registration with empty registration form, but then allow after filling', function () {
        submitRegistrationForm();
        checkRegistrationValidationErrorsPopup();
        checkPopupIsVisible(true);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'after invalid try');

        const email = 'invalid-registration-then-correct-and-try-again@shopsys.com';
        clearAndFillInRegstrationFormEmail(email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(password);
        submitRegistrationForm();
        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after valid try', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider }, { tid: TIDs.simple_navigation_image }],
        });
    });

    it('should disallow registration with invalid info, but then allow after correction', function () {
        const email = 'registration-with-existing-email@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email), false);

        clearAndFillInRegstrationFormEmail(email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(password);
        submitRegistrationForm();
        checkAndHideErrorToast('This email is already registered');

        clearAndFillInRegstrationFormEmail('registration-with-existing-email-different-email@shopsys.com');
        clearAndFillInRegistrationFormPasswords(password);
        submitRegistrationForm();
        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after valid try', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider }, { tid: TIDs.simple_navigation_image }],
        });
    });
});
