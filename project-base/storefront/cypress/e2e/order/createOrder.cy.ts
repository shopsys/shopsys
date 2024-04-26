import {
    fillEmailInThirdStep,
    fillCustomerInformationInThirdStep,
    fillBillingAdressInThirdStep,
    fillInNoteInThirdStep,
    clickOnSendOrderButton,
    checkFinishOrderPageAsUnloggedCustomerWithEmailWithExistingRegistration,
    clickOnOrderDetailButtonOnThankYouPage,
    checkFinishOrderPageAsUnregistredCustomer,
    fillRegistrationInfoAfterOrder,
    goToMyOrdersFromHeader,
} from './orderSupport';
import { transport, payment, customer1, orderNote, url, orderDetail, promoCode, password } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    changeElementText,
    checkAndHideSuccessToast,
    checkUrl,
    goToEditProfileFromHeader,
    loseFocus,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Create order tests', () => {
    beforeEach(() => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
    });

    it('should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail', () => {
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visitAndWaitForStableDOM(url.order.contactInformation);
        fillEmailInThirdStep(customer1.emailRegistered);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('create-order-unlogged-user-with-registerd-email-transport-to-home-cash-on-delivery');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnloggedCustomerWithEmailWithExistingRegistration();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-unlogged-user-with-registerd-email-transport-to-home-cash-on-delivery');
    });

    it('should create order as unlogged user (transport to home, cash on delivery) and check it in order detail', () => {
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visitAndWaitForStableDOM(url.order.contactInformation);
        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('create-order-unlogged-user-transport-to-home-cash-on-delivery');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnregistredCustomer();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-unlogged-user-transport-to-home-cash-on-delivery');
    });

    it('should create order as unlogged user (personal collection, cash) and check it in order detail', () => {
        cy.preselectTransportForTest(transport.personalCollection.uuid, transport.personalCollection.storeOstrava.uuid);
        cy.preselectPaymentForTest(payment.cash.uuid);

        cy.visitAndWaitForStableDOM(url.order.contactInformation);
        cy.url().should('contain', url.order.contactInformation);
        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('create-order-unlogged-user-personal-collection-cash');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnregistredCustomer();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-unlogged-user-personal-collection-cash');
    });

    it('should create order as unlogged user (PPL, credit cart) and check it in order detail', () => {
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);

        cy.visitAndWaitForStableDOM(url.order.contactInformation);
        cy.url().should('contain', url.order.contactInformation);
        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('create-order-unlogged-user-ppl-credit-card');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnregistredCustomer();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-unlogged-user-ppl-credit-card');
    });

    it('should create order with promo code and check it in order detail', () => {
        cy.addPromoCodeToCartForTest(promoCode);
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);
        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('create-order-with-promo-code');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnregistredCustomer();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-with-promo-code');
    });

    it('should register after order completion, and check that the just created order is in customer orders', () => {
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);
        fillEmailInThirdStep('after-order-registration@shopsys.com');
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        loseFocus();

        takeSnapshotAndCompare('create-order-and-register-afterwards');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnregistredCustomer();
        fillRegistrationInfoAfterOrder(password);
        cy.getByTID([TIDs.registration_after_order_submit_button]).click();

        checkAndHideSuccessToast('Your account has been created and you are logged in now');
        checkUrl('/');
        goToMyOrdersFromHeader();
        cy.getByTID([[TIDs.my_orders_link_, 0]]).click();
        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.breadcrumbs_tail, orderDetail.number);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);
        takeSnapshotAndCompare('my-orders-after-registration-after-order-creation');

        goToEditProfileFromHeader();
        takeSnapshotAndCompare('customer-edit-page-after-registration-after-order-creation');
    });

    it('should create order as logged-in user (transport to home, cash on delivery) and check it in order detail', () => {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('commonCustomer', 'create-order-as-logged-in-user@shopsys.com'),
            true,
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visitAndWaitForStableDOM(url.order.contactInformation);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('create-order-logged-in-user_before-create-order');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnloggedCustomerWithEmailWithExistingRegistration();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(TIDs.order_detail_number, orderDetail.numberHeading);
        changeElementText(TIDs.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('create-order-logged-in-user_order-detail');
    });
});
