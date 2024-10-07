import {
    goToRegistrationPageFromHeader,
    fillInRegstrationForm,
    checkRegistrationValidationErrorsPopup,
    submitRegistrationForm,
    clearAndFillInRegstrationFormEmail,
    clearAndFillInRegistrationFormPasswords,
} from './authenticationSupport';
import { password, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkAndHideErrorToast,
    checkAndHideSuccessToast,
    checkPopupIsVisible,
    checkUrl,
    goToEditProfileFromHeader,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Registration Tests (Basic)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('[Register B2C] register as a B2C customer', function () {
        goToRegistrationPageFromHeader();
        const email = 'register-as-b2c@shopsys.com';
        clearAndFillInRegstrationFormEmail(email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(password);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled registration form', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        submitRegistrationForm();
        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        checkUrl('/');
        cy.waitForStableAndInteractiveDOM();

        goToEditProfileFromHeader();
        checkUrl(url.customer.editProfile);
        takeSnapshotAndCompare(this.test?.title, 'customer edit page', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });
    });
});

describe('Registration Tests (Repeated Tries)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visitAndWaitForStableAndInteractiveDOM(url.registration);
    });

    it('[Empty Form] disallow registration with empty registration form, but then allow after filling', function () {
        submitRegistrationForm();
        checkRegistrationValidationErrorsPopup();
        checkPopupIsVisible(true);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'after invalid try', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

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

    it('[Invalid Info] disallow registration with invalid info, but then allow after correction', function () {
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
