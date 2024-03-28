import { changeSelectionOfPaymentByName } from './transportAndPaymentSupport';
import { payment, transport, url } from 'fixtures/demodata';
import { takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Payment select tests', () => {
    it('should select payment by cash', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        changeSelectionOfPaymentByName(payment.cash.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        cy.getByTID([TIDs.blocks_orderaction_next]).should('not.be.disabled');
        takeSnapshotAndCompare('payment-by-cash');

        cy.getByTID([TIDs.blocks_orderaction_next]).click();
        cy.url().should('contain', url.order.contactInformation);
    });

    it('should select a payment, deselect it, and then change the payment option', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.visitAndWaitForStableDOM(url.order.transportAndPayment);

        changeSelectionOfPaymentByName(payment.cash.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfPaymentByName(payment.cash.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        cy.getByTID([TIDs.blocks_orderaction_next]).should('not.be.disabled');
        takeSnapshotAndCompare('select-deselect-and-select-payment-again');

        cy.getByTID([TIDs.blocks_orderaction_next]).click();
        cy.url().should('contain', url.order.contactInformation);
    });
});
