import {
    changeOpeningHoursStatusToEmptyString,
    changeSelectionOfPaymentByName,
    changeSelectionOfTransportByName,
    chooseTransportPersonalCollectionAndStore,
} from './transportAndPaymentSupport';
import { payment, transport, url } from 'fixtures/demodata';
import { generateCreateOrderInput, generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    initializePersistStoreInLocalStorageToDefaultValues,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Last Order Transport And Payment Select Tests', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest();
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.createOrder(generateCreateOrderInput(registrationInput.email));
        cy.addProductToCartForTest();
    });

    it('[Preselect T&P] preselect transport and payment from last order for logged-in user', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        takeSnapshotAndCompare(this.test?.title, 'preselected transport and payment', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });

    it('[Change T&P And Preserve On Refresh] change preselected transport and payment from last order for logged-in user and keep the new selection after refresh', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after first change and refresh', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        chooseTransportPersonalCollectionAndStore(transport.personalCollection.storePardubice.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        changeSelectionOfPaymentByName(payment.cash.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        changeOpeningHoursStatusToEmptyString();
        takeSnapshotAndCompare(this.test?.title, 'after second change and refresh', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.opening_hours },
            ],
        });
    });
});
