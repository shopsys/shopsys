import {
    fillEmailInThirdStep,
    fillCustomerInformationInThirdStep,
    fillBillingAdressInThirdStep,
    fillInNoteInThirdStep,
    clickOnSendOrderButton,
    checkFinishOrderPageAsUnloggedCustomerWithEmailWithExistingRegistration,
    clickOnOrderDetailButtonOnThankYouPage,
    checkFinishOrderPageAsUnregistredCustomer,
} from './orderSupport';
import { DataTestIds } from 'dataTestIds';
import { transport, payment, customer1, orderNote, url, orderDetail } from 'fixtures/demodata';
import { changeElementText, loseFocus, takeSnapshotAndCompare } from 'support';

describe('Create order tests', () => {
    beforeEach(() => {
        cy.addProductToCartForTest().then((cartUuid) => cy.storeCartUuidInLocalStorage(cartUuid));
    });

    it('should create order as unlogged user with a registered email (transport to home, cash on delivery)', () => {
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);
        fillEmailInThirdStep(customer1.emailRegistered);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('create-order-unlogged-user-with-registerd-email-transport-to-home-cash-on-delivery');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnloggedCustomerWithEmailWithExistingRegistration();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(DataTestIds.order_detail_number, orderDetail.numberHeading);
        changeElementText(DataTestIds.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-unlogged-user-with-registerd-email-transport-to-home-cash-on-delivery');
    });

    it('should create order as unlogged user (transport to home, cash on delivery)', () => {
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);
        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('create-order-unlogged-user-transport-to-home-cash-on-delivery');
        clickOnSendOrderButton();

        checkFinishOrderPageAsUnregistredCustomer();
        clickOnOrderDetailButtonOnThankYouPage();

        changeElementText(DataTestIds.order_detail_number, orderDetail.numberHeading);
        changeElementText(DataTestIds.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-unlogged-user-transport-to-home-cash-on-delivery');
    });

    it('should create order as unlogged user (personal collection, cash)', () => {
        cy.preselectTransportForTest(transport.personalCollection.uuid, transport.personalCollection.storeOstrava.uuid);
        cy.preselectPaymentForTest(payment.cash.uuid);

        cy.visit(url.order.contactInformation);
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

        changeElementText(DataTestIds.order_detail_number, orderDetail.numberHeading);
        changeElementText(DataTestIds.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-unlogged-user-personal-collection-cash');
    });

    it('should create order as unlogged user (PPL, credit cart)', () => {
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);

        cy.visit(url.order.contactInformation);
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

        changeElementText(DataTestIds.order_detail_number, orderDetail.numberHeading);
        changeElementText(DataTestIds.order_detail_creation_date, orderDetail.creationDate, false);

        takeSnapshotAndCompare('order-detail-unlogged-user-ppl-credit-card');
    });
});
