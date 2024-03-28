import {
    fillEmailInThirdStep,
    fillCustomerInformationInThirdStep,
    fillBillingAdressInThirdStep,
    clearEmailInThirdStep,
    clearPostcodeInThirdStep,
    fillInNoteInThirdStep,
    clearAndFillDeliveryAdressInThirdStep,
    checkThatContactInformationWasRemovedFromLocalStorage,
} from './orderSupport';
import { DEFAULT_APP_STORE, customer1, deliveryAddress, orderNote, payment, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import { checkUrl, clickOnLabel, loseFocus, takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Contact information page tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should redirect to cart page and not display contact information form if cart is empty and user is not logged in', () => {
        cy.visit(url.order.contactInformation);

        cy.getByTID([TIDs.order_content_wrapper_skeleton]).should('exist');

        cy.getByTID([TIDs.cart_page_empty_cart_text]).should('exist');
        checkUrl(url.cart);

        takeSnapshotAndCompare('empty-cart-contact-information');
    });

    it('should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visit(url.order.contactInformation);

        cy.getByTID([TIDs.order_content_wrapper_skeleton]).should('exist');

        cy.getByTID([TIDs.pages_order_transport]).should('exist');
        checkUrl(url.order.transportAndPayment);

        takeSnapshotAndCompare('no-transport-and-payment-in-contact-information');
    });

    it('should redirect to cart page and not display contact information form if cart is empty and user is logged in', () => {
        cy.registerAsNewUser(generateCustomerRegistrationData());
        cy.visit(url.order.contactInformation);

        cy.getByTID([TIDs.order_content_wrapper_skeleton]).should('exist');

        cy.getByTID([TIDs.cart_page_empty_cart_text]).should('exist');
        checkUrl(url.cart);

        takeSnapshotAndCompare('empty-cart-contact-information-logged-in');
    });

    it('should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is logged in', () => {
        cy.registerAsNewUser(generateCustomerRegistrationData());
        cy.addProductToCartForTest();
        cy.visit(url.order.contactInformation);

        cy.getByTID([TIDs.order_content_wrapper_skeleton]).should('exist');

        cy.getByTID([TIDs.pages_order_transport]).should('exist');
        checkUrl(url.order.transportAndPayment);

        takeSnapshotAndCompare('no-transport-and-payment-in-contact-information-logged-in');
    });

    it('should keep filled contact information after page refresh', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);
        fillEmailInThirdStep(customer1.email);
        fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
        fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        cy.reload();

        takeSnapshotAndCompare('keep-filled-contact-information-after-reload');
    });

    it('should keep changed contact information after page refresh for logged-in user', () => {
        cy.registerAsNewUser(generateCustomerRegistrationData('refresh-page-contact-information@shopsys.com'));
        cy.addProductToCartForTest();
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);
        clearEmailInThirdStep();
        fillEmailInThirdStep('refresh-page-contact-information-changed@shopsys.com');
        fillCustomerInformationInThirdStep('123', ' changed', ' changed');
        clearPostcodeInThirdStep();
        fillBillingAdressInThirdStep(' changed', ' changed', '29292');
        fillInNoteInThirdStep(orderNote);
        loseFocus();

        takeSnapshotAndCompare('keep-changed-contact-information-after-reload');
    });

    it('should remove contact information after logout', () => {
        cy.registerAsNewUser(generateCustomerRegistrationData('remove-contact-information-after-logout@shopsys.com'));
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);

        cy.visit(url.order.contactInformation);

        clickOnLabel('contact-information-form-differentDeliveryAddress');
        clearAndFillDeliveryAdressInThirdStep(deliveryAddress);
        loseFocus();

        takeSnapshotAndCompare('should-remove-contact-information-after-logout_initially-filled');

        cy.logout();
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.reload();

        takeSnapshotAndCompare('should-remove-contact-information-after-logout');
        checkThatContactInformationWasRemovedFromLocalStorage();
    });
});
