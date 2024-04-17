import { changeSelectionOfPaymentByName, changeSelectionOfTransportByName } from './transportAndPaymentSupport';
import { DEFAULT_APP_STORE, payment, transport, url } from 'fixtures/demodata';
import { generateCreateOrderInput, generateCustomerRegistrationData } from 'fixtures/generators';
import { takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Last order transport and payment select tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });

        const registrationInput = generateCustomerRegistrationData();
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest();
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.createOrder(generateCreateOrderInput(registrationInput.email));

        cy.addProductToCartForTest();
    });

    it('should preselect transport and payment from last order for logged-in user', () => {
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        cy.getByTID([TIDs.pages_order_transport, TIDs.pages_order_selectitem_label_name]).should('be.visible');

        takeSnapshotAndCompare('preselected-last-order-transport-and-payment');
    });

    it('should be able to change preselected transport and payment from last order for logged-in user', () => {
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfTransportByName(transport.ppl.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfPaymentByName(payment.cash.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        cy.reloadAndWaitForStableDOM();

        takeSnapshotAndCompare('change-preselected-last-order-transport-and-payment');

        changeSelectionOfTransportByName(transport.ppl.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfTransportByName(transport.droneDelivery.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfPaymentByName(payment.payLater.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        takeSnapshotAndCompare('change-preselected-last-order-transport-and-payment-for-the-second-time');
    });
});
