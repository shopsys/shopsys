import {
    checkContactInformationFormIsNotVisible,
    checkContactInformationInThirdStep,
    checkSelectedDeliveryAddress,
    checkEmptyCartTextIsVisible,
    checkThatContactInformationWasRemovedFromLocalStorage,
    checkTransportSelectionIsVisible,
    clearPostcodeInThirdStep,
    clickOnSendOrderButton,
    fillBillingAdressInThirdStep,
    fillCustomerInformationInThirdStep,
    fillEmailInThirdStep,
    fillInNoteInThirdStep,
    clickAddNewAddressButton,
    fillAndSaveNewDeliveryAddressInPopup,
} from './orderSupport';
import { staticData, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkFormLineError,
    checkUrl,
    clickOnLabel,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.ORDER, SUBGROUP_INDEX);

describe('Contact Information Page Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Anon Empty Cart] should redirect to cart page and not display contact information form if cart is empty and user is not logged in', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        checkContactInformationFormIsNotVisible();
        checkEmptyCartTextIsVisible();
        checkUrl(url.cart);
        checkEmptyCartTextIsVisible();
    });

    it('[Anon Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        checkContactInformationFormIsNotVisible();
        checkTransportSelectionIsVisible();
        checkUrl(url.order.transportAndPayment);
    });

    it(
        '[Logged Empty Cart] should redirect to cart page and not display contact information form if cart is empty and user is logged in',
        { retries: { runMode: 0 } },
        () => {
            cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            checkContactInformationFormIsNotVisible();
            checkEmptyCartTextIsVisible();
            checkUrl(url.cart);
            checkEmptyCartTextIsVisible();
        },
    );

    it(
        '[Logged Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is logged in',
        { retries: { runMode: 0 } },
        () => {
            cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
            cy.addProductToCartForTest();
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            checkContactInformationFormIsNotVisible();
            checkTransportSelectionIsVisible();
            checkUrl(url.order.transportAndPayment);
        },
    );

    it('[Preserve Contact Form] should keep filled contact information after page refresh', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(staticData.customer1.email);
        fillCustomerInformationInThirdStep(
            staticData.customer1.phone,
            staticData.customer1.firstName,
            staticData.customer1.lastName,
        );
        fillBillingAdressInThirdStep(
            staticData.customer1.billingStreet,
            staticData.customer1.billingCity,
            staticData.customer1.billingPostCode,
        );
        fillInNoteInThirdStep(staticData.orderNote);
        loseFocus();
        const expectedContact = {
            email: staticData.customer1.email,
            telephone: staticData.customer1.phone,
            firstName: staticData.customer1.firstName,
            lastName: staticData.customer1.lastName,
            street: staticData.customer1.billingStreet,
            city: staticData.customer1.billingCity,
            postcode: staticData.customer1.billingPostCode,
            note: staticData.orderNote,
        };
        checkContactInformationInThirdStep(expectedContact);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        checkContactInformationInThirdStep(expectedContact);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(2), 'contact information page after reload', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });
    });

    it(
        '[Logged Preserve Contact Form] should keep changed contact information after page refresh for logged-in user',
        { retries: { runMode: 0 } },
        () => {
            const registrationInput = generateCustomerRegistrationData(
                'commonCustomer',
                'refresh-page-contact-information@shopsys.com',
            );
            cy.registerAsNewUser(registrationInput);
            cy.addProductToCartForTest();
            cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
            cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            cy.get('#contact-information-form-telephone').clear();
            fillCustomerInformationInThirdStep(staticData.customer1.phone, ' changed', ' changed');
            clearPostcodeInThirdStep();
            fillBillingAdressInThirdStep(' changed 123', ' changed', '29292');
            fillInNoteInThirdStep(staticData.orderNote);
            loseFocus();
            const expectedContact = {
                email: registrationInput.email,
                telephone: staticData.customer1.phone,
                firstName: `${registrationInput.firstName} changed`,
                lastName: `${registrationInput.lastName} changed`,
                street: `${registrationInput.street} changed 123`,
                city: `${registrationInput.city} changed`,
                postcode: '29292',
                note: staticData.orderNote,
            };
            checkContactInformationInThirdStep(expectedContact);
            cy.reloadAndWaitForStableAndInteractiveDOM();
            checkContactInformationInThirdStep(expectedContact);
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(3), 'contact information page after reload', {
                blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
            });
        },
    );

    it('[Logout Clear Form] should remove contact information after logout', { retries: { runMode: 0 } }, () => {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('commonCustomer', 'remove-contact-information-after-logout@shopsys.com'),
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        clickAddNewAddressButton();
        fillAndSaveNewDeliveryAddressInPopup(staticData.deliveryAddress);
        checkSelectedDeliveryAddress(staticData.deliveryAddress);

        cy.logout();
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(5), 'empty contact information form after logout', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });
        checkThatContactInformationWasRemovedFromLocalStorage();
        checkContactInformationInThirdStep({
            email: '',
            telephone: '',
            firstName: '',
            lastName: '',
            street: '',
            city: '',
            postcode: '',
            note: '',
        });
    });

    it('[Invalid Email] should not reopen the closed error popup while the invalid email is being corrected', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(staticData.invalidEmail);
        fillCustomerInformationInThirdStep(
            staticData.customer1.phone,
            staticData.customer1.firstName,
            staticData.customer1.lastName,
        );
        fillBillingAdressInThirdStep(
            staticData.customer1.billingStreet,
            staticData.customer1.billingCity,
            staticData.customer1.billingPostCode,
        );
        clickOnSendOrderButton();

        cy.getByTID([TIDs.layout_popup]).should('be.visible');
        checkFormLineError('Please enter a valid email. Example: email@example.com');

        cy.realPress('{esc}');
        cy.getByTID([TIDs.layout_popup]).should('not.exist');

        cy.get('#contact-information-form-email').type('shopsys');
        checkFormLineError('Please enter a valid email. Example: email@example.com');
        cy.getByTID([TIDs.layout_popup]).should('not.exist');
    });

    it('[Invalid Email Prefill] should not report the invalid email restored from local storage until the field is left', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(staticData.invalidEmail);
        cy.reloadAndWaitForStableAndInteractiveDOM();

        cy.get('#contact-information-form-email').should('have.value', staticData.invalidEmail);
        cy.getByTID([TIDs.form_line_error]).should('not.exist');

        cy.get('#contact-information-form-email').focus();
        loseFocus();

        checkFormLineError('Please enter a valid email. Example: email@example.com');
    });
});
