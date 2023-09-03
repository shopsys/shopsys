import { changeSelectionOfPaymentByName } from './transportAndPaymentSupport';
import { payment, transport, url } from 'fixtures/demodata';
import { takeSnapshotAndCompare } from 'support';

describe('Payment select tests', () => {
    it('should select payment by cash', () => {
        cy.addProductToCartForTest().then((cartUuid) => cy.storeCartUuidInLocalStorage(cartUuid));
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.visit(url.order.transportAndPayment);

        changeSelectionOfPaymentByName(payment.cash.name);
        cy.getByDataTestId('loader-overlay').should('not.exist');

        cy.getByDataTestId('blocks-orderaction-next').should('not.be.disabled');
        takeSnapshotAndCompare('payment-by-cash');

        cy.getByDataTestId('blocks-orderaction-next').click();
        cy.url().should('contain', url.order.contactInformation);
    });

    it('should select a payment, deselect it, and then change the payment option', () => {
        cy.addProductToCartForTest().then((cartUuid) => cy.storeCartUuidInLocalStorage(cartUuid));
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.visit(url.order.transportAndPayment);

        changeSelectionOfPaymentByName(payment.cash.name);
        cy.getByDataTestId('loader-overlay').should('not.exist');
        changeSelectionOfPaymentByName(payment.cash.name);
        cy.getByDataTestId('loader-overlay').should('not.exist');
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        cy.getByDataTestId('loader-overlay').should('not.exist');

        cy.getByDataTestId('blocks-orderaction-next').should('not.be.disabled');
        takeSnapshotAndCompare('select-deselect-and-select-payment-again');

        cy.getByDataTestId('blocks-orderaction-next').click();
        cy.url().should('contain', url.order.contactInformation);
    });
});
