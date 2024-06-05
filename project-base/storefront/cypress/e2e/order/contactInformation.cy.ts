import {
    fillEmailInThirdStep,
    fillCustomerInformationInThirdStep,
    fillBillingAdressInThirdStep,
    fillInNoteInThirdStep,
    clearEmailInThirdStep,
    clearPostcodeInThirdStep,
    clearAndFillDeliveryAdressInThirdStep,
    checkThatContactInformationWasRemovedFromLocalStorage,
    checkEmptyCartTextIsVisible,
    checkTransportSelectionIsNotVisible,
    checkTransportSelectionIsVisible,
    checkContactInformationFormIsNotVisible,
} from './orderSupport';
import { transport, payment, customer1, orderNote, deliveryAddress, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkUrl,
    takeSnapshotAndCompare,
    loseFocus,
    clickOnLabel,
    initializePersistStoreInLocalStorageToDefaultValues,
} from 'support';
import { TIDs } from 'tids';

describe('Contact information page tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should redirect to cart page and not display contact information form if cart is empty and user is not logged in', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        checkTransportSelectionIsNotVisible();
        checkEmptyCartTextIsVisible();
        checkUrl(url.cart);
        takeSnapshotAndCompare(this.test?.title, 'empty cart page');
    });

    it('should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        checkContactInformationFormIsNotVisible();
        checkTransportSelectionIsVisible();
        checkUrl(url.order.transportAndPayment);
        takeSnapshotAndCompare(this.test?.title, 'transport and payment page', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });

    it('should redirect to cart page and not display contact information form if cart is empty and user is logged in', function () {
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        checkTransportSelectionIsNotVisible();
        checkEmptyCartTextIsVisible();
        checkUrl(url.cart);
        takeSnapshotAndCompare(this.test?.title, 'empty cart page');
    });

    it('should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is logged in', function () {
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
        cy.addProductToCartForTest();
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        checkContactInformationFormIsNotVisible();
        checkTransportSelectionIsVisible();
        checkUrl(url.order.transportAndPayment);
        takeSnapshotAndCompare(this.test?.title, 'transport and payment page', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });

    it('should keep filled contact information after page refresh', function () {
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
        takeSnapshotAndCompare(this.test?.title, 'contact information page after reload', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });

    it('should keep changed contact information after page refresh for logged-in user', function () {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('commonCustomer', 'refresh-page-contact-information@shopsys.com'),
        );
        cy.addProductToCartForTest();
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clearEmailInThirdStep();
        fillEmailInThirdStep('refresh-page-contact-information-changed@shopsys.com');
        fillCustomerInformationInThirdStep('123', ' changed', ' changed');
        clearPostcodeInThirdStep();
        fillBillingAdressInThirdStep(' changed', ' changed', '29292');
        fillInNoteInThirdStep(orderNote);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'contact information page after reload', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });

    it('should remove contact information after logout', function () {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('commonCustomer', 'remove-contact-information-after-logout@shopsys.com'),
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled contact information form before logout', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        cy.logout();
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'empty contact information form after logout', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
        checkThatContactInformationWasRemovedFromLocalStorage();
    });
});
