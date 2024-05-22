import {
    clearAndFillDeliveryAdressInThirdStep,
    clearAndFillDeliveryContactInThirdStep,
    registerAndCreateOrderForDeliveryAddressTests,
    fillBillingInfoForDeliveryAddressTests,
    clickOnOrderDetailButtonOnThankYouPage,
    clickOnSendOrderButton,
    changeOrderConfirmationDynamicPartsToStaticDemodata,
    changeOrderDetailDynamicPartsToStaticDemodata,
} from './orderSupport';
import { deliveryAddress, payment, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    clickOnLabel,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Create order with delivery address tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        fillBillingInfoForDeliveryAddressTests();
    });

    it('should keep filled delivery address after page refresh', function () {
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'contact information form after refresh', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });

    it('should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again', function () {
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        cy.wait(500);
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form after checking again', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });
});

describe('Delivery address in order tests (logged-in user)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should keep filled delivery address for logged-in user after page refresh', function () {
        cy.registerAsNewUser(
            generateCustomerRegistrationData(
                'commonCustomer',
                'keep-filled-delivery-address-after-page-refresh-logged-in@shopsys.com',
            ),
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'contact information form after refresh', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });

    it('should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery address and then checking it again', function () {
        cy.registerAsNewUser(
            generateCustomerRegistrationData(
                'commonCustomer',
                'keep-filled-delivery-address-logged-in-after-unchecking-and-checking@shopsys.com',
            ),
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        cy.wait(500);
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form after checking again', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });

    it('should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh', function () {
        registerAndCreateOrderForDeliveryAddressTests(
            'first-select-saved-then-fill-and-keep-filled-after-refresh@shopsys.com',
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'with default address', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnLabel('contact-information-formdeliveryAddressUuid-new-delivery-address');
        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'changed contact information after refresh', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });

    it('should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled', function () {
        registerAndCreateOrderForDeliveryAddressTests(
            'first-select-saved-then-change-to-new-then-to-saved-and-to-new-again-logged-in@shopsys.com',
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'with default address', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnLabel('contact-information-formdeliveryAddressUuid-new-delivery-address');
        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'with changed delivery address', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnLabel('contact-information-formdeliveryAddressUuid0');
        clickOnLabel('contact-information-formdeliveryAddressUuid-new-delivery-address');
        takeSnapshotAndCompare(this.test?.title, 'with changed delivery address after switching back from default', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });
});

describe('Delivery address in order tests (with pickup point)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.personalCollection.uuid, transport.personalCollection.storeOstrava.uuid);
        cy.preselectPaymentForTest(payment.cash.uuid);

        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        fillBillingInfoForDeliveryAddressTests();
    });

    it('should prefill delivery address from selected pickup point and keep delivery contact after refresh', function () {
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clearAndFillDeliveryContactInThirdStep(deliveryAddress);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'contact information form after refresh', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });

    it('should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again', function () {
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clearAndFillDeliveryContactInThirdStep(deliveryAddress);
        loseFocus();
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        cy.wait(500);
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'after checking again', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });
});

describe('Delivery address in order tests (with pickup point, logged-in user)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then keep the filled delivery information after refresh', function () {
        registerAndCreateOrderForDeliveryAddressTests(
            'no-prefill-contact-information-with-selected-pickup-place@shopsys.com',
            transport.personalCollection.uuid,
            transport.personalCollection.storeOstrava.uuid,
            payment.cash.uuid,
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clearAndFillDeliveryContactInThirdStep(deliveryAddress);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'contact information form after refresh', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });

    it('should not prefill delivery contact for logged-in user with saved address and pickup point, but keep filled delivery information after unchecking and checking checkbox for different delivery address', function () {
        registerAndCreateOrderForDeliveryAddressTests(
            'keep-delivery-address-with-saved-after-uncheck@shopsys.com',
            transport.personalCollection.uuid,
            transport.personalCollection.storeOstrava.uuid,
            payment.cash.uuid,
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clearAndFillDeliveryContactInThirdStep(deliveryAddress);
        loseFocus();
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        cy.wait(500);
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        takeSnapshotAndCompare(this.test?.title, 'after checking again', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation');

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail');
    });
});
