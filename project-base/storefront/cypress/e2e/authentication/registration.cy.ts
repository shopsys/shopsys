import {
    goToRegistrationPageFromHeader,
    fillInRegstrationForm,
    checkRegistrationValidationErrorsPopup,
} from './authenticationSupport';
import { DEFAULT_APP_STORE, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkAndHideErrorToast,
    checkAndHideSuccessToast,
    checkUrl,
    goToEditProfileFromHeader,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Registration tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should register as a B2C customer', () => {
        cy.visit('/');

        goToRegistrationPageFromHeader();
        fillInRegstrationForm('commonCustomer', 'registraion-as-b2c@shopsys.com');

        takeSnapshotAndCompare('b2c-registration-page');

        cy.getByTID([TIDs.registration_submit_button]).click();

        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        checkUrl('/');
        goToEditProfileFromHeader();

        takeSnapshotAndCompare('customer-edit-page-after-b2c-registration');
    });

    it('should register as a B2B customer', () => {
        cy.visit('/');

        goToRegistrationPageFromHeader();
        fillInRegstrationForm('companyCustomer', 'registraion-as-b2b@shopsys.com');

        takeSnapshotAndCompare('b2b-registration-page');

        cy.getByTID([TIDs.registration_submit_button]).click();

        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        checkUrl('/');
        goToEditProfileFromHeader();

        takeSnapshotAndCompare('customer-edit-page-after-b2b-registration');
    });

    it('should disallow registration with invalid info, but then allow after correction', () => {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('commonCustomer', 'repeated-identical-email-registration@shopsys.com'),
            false,
        );
        cy.visit(url.registration);

        cy.getByTID([TIDs.registration_submit_button]).click();
        checkRegistrationValidationErrorsPopup();
        cy.getByTID([TIDs.layout_popup]).find('button').click();
        takeSnapshotAndCompare('invalid-registration-page');

        fillInRegstrationForm('commonCustomer', 'repeated-identical-email-registration@shopsys.com');
        cy.getByTID([TIDs.registration_submit_button]).click();
        checkAndHideErrorToast('This email is already registered');
    });
});
