import {
    checkContactInformationFormIsNotVisible,
    checkEmptyCartTextIsVisible,
    checkThatContactInformationWasRemovedFromLocalStorage,
    checkTransportSelectionIsNotVisible,
    checkTransportSelectionIsVisible,
    clearPostcodeInThirdStep,
    fillBillingAdressInThirdStep,
    fillCustomerInformationInThirdStep,
    fillEmailInThirdStep,
    fillInNoteInThirdStep,
    clickAddNewAddressButton,
    fillAndSaveNewDeliveryAddressInPopup,
} from './orderSupport';
import { changeExpectedDeliveryDateMessagesToStaticDemodata } from 'e2e/transportAndPayment/transportAndPaymentSupport';
import { staticData, url } from 'fixtures/demodata';
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
        changeExpectedDeliveryDateMessagesToStaticDemodata();
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
            changeExpectedDeliveryDateMessagesToStaticDemodata();
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
            cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
            cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            fillCustomerInformationInThirdStep('123', ' changed', ' changed');
            clearPostcodeInThirdStep();
            fillBillingAdressInThirdStep(' changed 123', ' changed', '29292');
            fillInNoteInThirdStep(staticData.orderNote);
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
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        clickAddNewAddressButton();
        fillAndSaveNewDeliveryAddressInPopup(staticData.deliveryAddress);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form before logout', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        cy.logout();
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'empty contact information form after logout', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });
        checkThatContactInformationWasRemovedFromLocalStorage();
    });
});
