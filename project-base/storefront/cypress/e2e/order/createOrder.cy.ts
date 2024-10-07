import {
    fillEmailInThirdStep,
    fillCustomerInformationInThirdStep,
    fillBillingAdressInThirdStep,
    fillInNoteInThirdStep,
    clickOnSendOrderButton,
    clickOnOrderDetailButtonOnThankYouPage,
    fillRegistrationInfoAfterOrder,
    changeOrderDetailDynamicPartsToStaticDemodata,
    changeOrderConfirmationDynamicPartsToStaticDemodata,
    submitRegistrationFormAfterOrder,
    goToOrderDetailFromOrderList,
    mouseOverUserMenuButton,
} from './orderSupport';
import { transport, payment, customer1, orderNote, url, promoCode, password } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkAndHideSuccessToast,
    checkUrl,
    goToEditProfileFromHeader,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Create Order Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
    });

    it('[Anon Registered Home Cash] create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail', function () {
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(customer1.emailRegistered);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled contact information form', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail', { blackout: [{ tid: TIDs.footer_social_links }] });
    });

    it('[Anon Home Cash] create order as unlogged user (transport to home, cash on delivery) and check it in order detail', function () {
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled contact information form', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail', { blackout: [{ tid: TIDs.footer_social_links }] });
    });

    it('[Anon Collect Cash] create order as unlogged user (personal collection, cash) and check it in order detail', function () {
        cy.preselectTransportForTest(transport.personalCollection.uuid, transport.personalCollection.storeOstrava.uuid);
        cy.preselectPaymentForTest(payment.cash.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled contact information form', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail', { blackout: [{ tid: TIDs.footer_social_links }] });
    });

    it('[Anon PPL Card] create order as unlogged user (PPL, credit card) and check it in order detail', function () {
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled contact information form', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail', { blackout: [{ tid: TIDs.footer_social_links }] });
    });

    it('[Anon Promo Code] create order with promo code and check it in order detail', function () {
        cy.addPromoCodeToCartForTest(promoCode);
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled contact information form', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail', { blackout: [{ tid: TIDs.footer_social_links }] });
    });

    it('[Register After Order] register after order completion, and check that the just created order is in customer orders', function () {
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillEmailInThirdStep('after-order-registration@shopsys.com');
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled contact information form', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        fillRegistrationInfoAfterOrder(password);
        submitRegistrationFormAfterOrder();
        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        cy.waitForStableAndInteractiveDOM();
        checkUrl('/');

        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);
        goToOrderDetailFromOrderList();
        changeOrderDetailDynamicPartsToStaticDemodata(true);
        takeSnapshotAndCompare(this.test?.title, 'order detail', { blackout: [{ tid: TIDs.footer_social_links }] });

        goToEditProfileFromHeader();
        takeSnapshotAndCompare(this.test?.title, 'customer edit page', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });
    });

    it('[Logged Home Cash] create order as logged-in user (transport to home, cash on delivery) and check it in order detail', function () {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('commonCustomer', 'create-order-as-logged-in-user@shopsys.com'),
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        fillInNoteInThirdStep(orderNote);
        loseFocus();
        takeSnapshotAndCompare(this.test?.title, 'filled contact information form', {
            blackout: [
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        mouseOverUserMenuButton();
        takeSnapshotAndCompare(this.test?.title, 'order confirmation', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'order detail', { blackout: [{ tid: TIDs.footer_social_links }] });
    });
});
