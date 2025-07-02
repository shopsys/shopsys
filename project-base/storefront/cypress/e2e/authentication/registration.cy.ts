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
    checkIsUserLoggedIn,
    checkPopupIsVisible,
    checkUrl,
    getSnapshotIndexingFunction,
    goToEditProfileFromHeader,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.AUTHENTICATION, SUBGROUP_INDEX);

describe('Registration Tests (Basic)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('[Register B2C] should register as a B2C customer', function () {
        goToRegistrationPageFromHeader();
        const email = 'register-as-b2c@shopsys.com';
        clearAndFillInRegstrationFormEmail(email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(password);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled registration form', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        submitRegistrationForm();
        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        checkUrl('/');
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

describe('Registration Tests (Repeated Tries)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visitAndWaitForStableAndInteractiveDOM(url.registration);
    });

    it('[Empty Form] should disallow registration with empty registration form, but then allow after filling', function () {
        submitRegistrationForm();
        checkRegistrationValidationErrorsPopup();
        checkPopupIsVisible(true);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after invalid try', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        const email = 'invalid-registration-then-correct-and-try-again@shopsys.com';
        clearAndFillInRegstrationFormEmail(email);
        fillInRegstrationForm('commonCustomer', email);
        clearAndFillInRegistrationFormPasswords(password);
        submitRegistrationForm();
        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedIn();
    });

    it('[Invalid Info] should disallow registration with invalid info, but then allow after correction', function () {
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
        checkIsUserLoggedIn();
    });
});
