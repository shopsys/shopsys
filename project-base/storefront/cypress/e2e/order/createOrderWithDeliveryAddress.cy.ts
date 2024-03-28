import {
    clearAndFillDeliveryAdressInThirdStep,
    clearAndFillDeliveryContactInThirdStep,
    registerAndCreateOrderForDeliveryAddressTests,
    fillBillingInfoForDeliveryAddressTests,
    checkFinishOrderPageAsUnregistredCustomer,
    clickOnOrderDetailButtonOnThankYouPage,
    clickOnSendOrderButton,
} from './orderSupport';
import { DEFAULT_APP_STORE, deliveryAddress, orderDetail, payment, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import { changeElementText, clickOnLabel, loseFocus, takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Create order with delivery address tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);
        fillBillingInfoForDeliveryAddressTests();
    });

    it('should keep filled delivery address after page refresh', () => {
        clickOnLabel('contact-information-form-differentDeliveryAddress');

        takeSnapshotAndCompare('order-with-delivery-address_basic-1_initially-empty');

        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();

        cy.reload();

        takeSnapshotAndCompare('order-with-delivery-address_basic-1');

        clickOnSendOrderButton();

        checkFinishOrderPageAsUnregistredCustomer();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-with-delivery-address_basic-1_order-detail');
    });

    it(
        'should keep filled delivery address after unchecking the checkbox for different delivery address and ' +
            'then checking it again',
        () => {
            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_basic-2_initially-empty');

            clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
            loseFocus();

            clickOnLabel('contact-information-form-differentDeliveryAddress');
            cy.wait(500);
            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_basic-2');

            clickOnSendOrderButton();

            checkFinishOrderPageAsUnregistredCustomer();
            clickOnOrderDetailButtonOnThankYouPage();

            changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
            changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

            takeSnapshotAndCompare('order-with-delivery-address_basic-2_order-detail');
        },
    );
});

describe('Delivery address in order tests (logged-in user)', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should keep filled delivery address for logged-in user after page refresh', () => {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('keep-filled-delivery-address-logged-in-after-page-refresh@shopsys.com'),
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);

        clickOnLabel('contact-information-form-differentDeliveryAddress');

        takeSnapshotAndCompare('order-with-delivery-address_logged-in-1_initially-empty');

        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();

        cy.reload();

        takeSnapshotAndCompare('order-with-delivery-address_logged-in-1');

        clickOnSendOrderButton();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-with-delivery-address_logged-in-1_order-detail');
    });

    it(
        'should keep filled delivery address for logged-in user after unchecking the checkbox for different delivery ' +
            'address and then checking it again',
        () => {
            cy.registerAsNewUser(
                generateCustomerRegistrationData(
                    'keep-filled-delivery-address-logged-in-after-unchecking-and-checking@shopsys.com',
                ),
            );
            cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
            cy.preselectTransportForTest(transport.czechPost.uuid);
            cy.preselectPaymentForTest(payment.onDelivery.uuid);

            cy.visit(url.order.contactInformation);

            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-2_initially-empty');

            clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
            loseFocus();

            clickOnLabel('contact-information-form-differentDeliveryAddress');
            cy.wait(500);
            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-2');

            clickOnSendOrderButton();
            clickOnOrderDetailButtonOnThankYouPage();

            changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
            changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-2_order-detail');
        },
    );

    it(
        'should first select saved default delivery address for logged-in user, but then fill and keep new delivery ' +
            'address after refresh',
        () => {
            registerAndCreateOrderForDeliveryAddressTests(
                'first-select-saved-then-fill-and-keep-filled-after-refresh@shopsys.com',
            );

            cy.visit(url.order.contactInformation);

            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-3_default_saved_address');

            clickOnLabel('contact-information-formdeliveryAddressUuid-new-delivery-address');
            clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
            loseFocus();

            cy.reload();

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-3');

            clickOnSendOrderButton();
            clickOnOrderDetailButtonOnThankYouPage();

            changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
            changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-3_order-detail');
        },
    );

    it(
        'should first select saved default delivery address for logged-in user, then fill new delivery address,' +
            'then change it to a saved one and back to the new address which should stay filled',
        () => {
            registerAndCreateOrderForDeliveryAddressTests(
                'first-select-saved-then-change-to-new-then-to-saved-and-to-new-again@shopsys.com',
            );

            cy.visit(url.order.contactInformation);

            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-4_default_saved_address');

            clickOnLabel('contact-information-formdeliveryAddressUuid-new-delivery-address');
            clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
            loseFocus();

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-4');

            clickOnLabel('contact-information-formdeliveryAddressUuid0');
            clickOnLabel('contact-information-formdeliveryAddressUuid-new-delivery-address');

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-4');

            clickOnSendOrderButton();
            clickOnOrderDetailButtonOnThankYouPage();

            changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
            changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

            takeSnapshotAndCompare('order-with-delivery-address_logged-in-4_order-detail');
        },
    );
});

describe('Delivery address in order tests (with pickup point)', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.personalCollection.uuid, transport.personalCollection.storeOstrava.uuid);
        cy.preselectPaymentForTest(payment.cash.uuid);

        cy.visit(url.order.contactInformation);
        fillBillingInfoForDeliveryAddressTests();
    });

    it('should prefill delivery address from selected pickup point and keep delivery contact after refresh', () => {
        clickOnLabel('contact-information-form-differentDeliveryAddress');

        takeSnapshotAndCompare('order-with-delivery-address_pickup-point-1_initially-empty');

        clearAndFillDeliveryContactInThirdStep(deliveryAddress);
        loseFocus();

        cy.reload();

        takeSnapshotAndCompare('order-with-delivery-address_pickup-point-1');

        clickOnSendOrderButton();

        checkFinishOrderPageAsUnregistredCustomer();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-with-delivery-address_pickup-point-1_order-detail');
    });

    it(
        'should prefill delivery address from selected pickup point and keep delivery contact after unchecking the ' +
            'checkbox for different delivery contact and then checking it again',
        () => {
            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-2_initially-empty');

            clearAndFillDeliveryContactInThirdStep(deliveryAddress);
            loseFocus();

            clickOnLabel('contact-information-form-differentDeliveryAddress');
            cy.wait(500);
            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-2');

            clickOnSendOrderButton();

            checkFinishOrderPageAsUnregistredCustomer();
            clickOnOrderDetailButtonOnThankYouPage();

            changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
            changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-2_order-detail');
        },
    );
});

describe('Delivery address in order tests (with pickup point, logged-in user)', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it(
        'should not prefill delivery contact for logged-in user with saved address and with selected pickup point, ' +
            'and then keep the filled delivery information after refresh',
        () => {
            registerAndCreateOrderForDeliveryAddressTests(
                'no-prefill-contact-information-with-selected-pickup-place@shopsys.com',
                transport.personalCollection.uuid,
                transport.personalCollection.storeOstrava.uuid,
                payment.cash.uuid,
            );

            cy.visit(url.order.contactInformation);

            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-logged-in-1_initially-empty');

            clearAndFillDeliveryContactInThirdStep(deliveryAddress);
            loseFocus();

            cy.reload();

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-logged-in-1');

            clickOnSendOrderButton();
            clickOnOrderDetailButtonOnThankYouPage();

            changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
            changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-logged-in-1_order-detail');
        },
    );

    it(
        'should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then' +
            ' keep the filled delivery information after unchecking and checking the checkbox for different delivery address',
        () => {
            registerAndCreateOrderForDeliveryAddressTests(
                'keep-delivery-address-with-saved-after-uncheck@shopsys.com',
                transport.personalCollection.uuid,
                transport.personalCollection.storeOstrava.uuid,
                payment.cash.uuid,
            );

            cy.visit(url.order.contactInformation);

            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-logged-in-2_initially-empty');

            clearAndFillDeliveryContactInThirdStep(deliveryAddress);
            loseFocus();

            clickOnLabel('contact-information-form-differentDeliveryAddress');
            cy.wait(500);
            clickOnLabel('contact-information-form-differentDeliveryAddress');

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-logged-in-2');

            clickOnSendOrderButton();
            clickOnOrderDetailButtonOnThankYouPage();

            changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
            changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

            takeSnapshotAndCompare('order-with-delivery-address_pickup-point-logged-in-2_order-detail');
        },
    );
});
