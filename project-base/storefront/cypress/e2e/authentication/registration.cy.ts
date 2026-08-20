import {
    goToRegistrationPageFromHeader,
    goToRegistrationPageFromFixedHeader,
    fillInRegstrationForm,
    submitRegistrationForm,
    clearAndFillInRegstrationFormEmail,
    clearAndFillInRegistrationFormPasswords,
    checkRegistrationValidationErrors,
    waitForRegistrationRedirect,
} from './authenticationSupport';
import { staticData, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkAndHideSuccessToast,
    checkFormLineError,
    checkIsUserLoggedIn,
    checkUrl,
    getSnapshotIndexingFunction,
    goToEditProfileFromHeader,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
    translations,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.AUTHENTICATION, SUBGROUP_INDEX);

describe('Registration Tests (Basic)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('[Sticky Header] should navigate to registration and close the account menu', () => {
        cy.scrollTo('bottom');
        cy.getByTID([TIDs.fixed_header]).should('be.visible');

        goToRegistrationPageFromFixedHeader();
    });

    it('[Register B2C] should register as a B2C customer', function () {
        goToRegistrationPageFromHeader();
        const email = 'register-as-b2c@shopsys.com';
        clearAndFillInRegstrationFormEmail(email, translations.placeholder.email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(staticData.user.password);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled registration form', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        submitRegistrationForm();
        waitForRegistrationRedirect();
        checkAndHideSuccessToast(translations.toast.success.accountCreated);
        cy.waitForStableAndInteractiveDOM();

        goToEditProfileFromHeader();
        checkUrl(url.customer.editProfile);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'customer edit page', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});

describe('Registration Tests (B2B)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('[Register B2B] should register as a B2B (company) customer', function () {
        goToRegistrationPageFromHeader();
        const email = 'register-as-b2b@shopsys.com';
        clearAndFillInRegstrationFormEmail(email, translations.placeholder.email);
        fillInRegstrationForm('companyCustomer', email);
        clearAndFillInRegistrationFormPasswords(staticData.user.password);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled b2b registration form', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        submitRegistrationForm();
        waitForRegistrationRedirect();
        checkAndHideSuccessToast(translations.toast.success.accountCreated);
        cy.waitForStableAndInteractiveDOM();

        goToEditProfileFromHeader();
        checkUrl(url.customer.editProfile);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'b2b customer edit page', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});

describe('Registration Tests (Repeated Tries)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visitAndWaitForStableAndInteractiveDOM(url.registration);
    });

    it('[Empty Form] should disallow registration with empty registration form, show flash message, then allow after filling', function () {
        submitRegistrationForm();
        checkRegistrationValidationErrors();
        cy.getByTID([TIDs.form_line_error]).should('have.length.greaterThan', 0);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after invalid try', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        const email = 'invalid-registration-then-correct-and-try-again@shopsys.com';
        clearAndFillInRegstrationFormEmail(email, translations.placeholder.email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(staticData.user.password);
        submitRegistrationForm();
        waitForRegistrationRedirect();
        checkAndHideSuccessToast(translations.toast.success.accountCreated);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedIn();
    });

    it('[Invalid Info] should disallow registration with invalid info, but then allow after correction', function () {
        const email = 'registration-with-existing-email@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email), false);

        clearAndFillInRegstrationFormEmail(email, translations.placeholder.email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(staticData.user.password);
        submitRegistrationForm();
        checkFormLineError('This email is already registered');

        clearAndFillInRegstrationFormEmail(
            'registration-with-existing-email-different-email@shopsys.com',
            translations.placeholder.email,
        );
        clearAndFillInRegistrationFormPasswords(staticData.user.password);
        submitRegistrationForm();
        waitForRegistrationRedirect();
        checkAndHideSuccessToast(translations.toast.success.accountCreated);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedIn();
    });
});
