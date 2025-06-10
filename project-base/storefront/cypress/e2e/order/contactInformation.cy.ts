import {
    checkContactInformationFormIsNotVisible,
    checkEmptyCartTextIsVisible,
    checkThatContactInformationWasRemovedFromLocalStorage,
    checkTransportSelectionIsNotVisible,
    checkTransportSelectionIsVisible,
    clearAndFillDeliveryAdressInThirdStep,
    clearPostcodeInThirdStep,
    fillBillingAdressInThirdStep,
    fillCustomerInformationInThirdStep,
    fillEmailInThirdStep,
    fillInNoteInThirdStep,
} from './orderSupport';
import { customer1, deliveryAddress, orderNote, payment, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
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

    it('[Anon Empty Cart] should redirect to cart page and not display contact information form if cart is empty and user is not logged in', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        checkTransportSelectionIsNotVisible();
        checkEmptyCartTextIsVisible();
        checkUrl(url.cart);
        checkEmptyCartTextIsVisible();
    });

    it('[Anon Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        checkContactInformationFormIsNotVisible();
        checkTransportSelectionIsVisible();
        checkUrl(url.order.transportAndPayment);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'transport and payment page', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it(
        '[Logged Empty Cart] should redirect to cart page and not display contact information form if cart is empty and user is logged in',
        { retries: { runMode: 0 } },
        function () {
            cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            checkTransportSelectionIsNotVisible();
            checkEmptyCartTextIsVisible();
            checkUrl(url.cart);
            checkEmptyCartTextIsVisible();
        },
    );

    it(
        '[Logged Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is logged in',
        { retries: { runMode: 0 } },
        function () {
            cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
            cy.addProductToCartForTest();
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            checkContactInformationFormIsNotVisible();
            checkTransportSelectionIsVisible();
            checkUrl(url.order.transportAndPayment);
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'transport and payment page', {
                blackout: [
                    { tid: TIDs.transport_and_payment_list_item_image },
                    { tid: TIDs.order_summary_cart_item_image },
                    { tid: TIDs.footer_copyright },
                ],
            });
        },
    );

    it('[Preserve Contact Form] should keep filled contact information after page refresh', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information page after reload', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });
    });

    it(
        '[Logged Preserve Contact Form] should keep changed contact information after page refresh for logged-in user',
        { retries: { runMode: 0 } },
        function () {
            cy.registerAsNewUser(
                generateCustomerRegistrationData('commonCustomer', 'refresh-page-contact-information@shopsys.com'),
            );
            cy.addProductToCartForTest();
            cy.preselectTransportForTest(transport.czechPost.uuid);
            cy.preselectPaymentForTest(payment.onDelivery.uuid);
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            fillCustomerInformationInThirdStep('123', ' changed', ' changed');
            clearPostcodeInThirdStep();
            fillBillingAdressInThirdStep(' changed', ' changed', '29292');
            fillInNoteInThirdStep(orderNote);
            loseFocus();
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information page after reload', {
                blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
            });
        },
    );

    it('[Logout Clear Form] should remove contact information after logout', { retries: { runMode: 0 } }, function () {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('commonCustomer', 'remove-contact-information-after-logout@shopsys.com'),
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form before logout', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        cy.logout();
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'empty contact information form after logout', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });
        checkThatContactInformationWasRemovedFromLocalStorage();
    });
});
