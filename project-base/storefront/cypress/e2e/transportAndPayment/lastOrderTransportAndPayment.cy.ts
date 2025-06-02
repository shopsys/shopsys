import {
    changeSelectionOfPaymentByName,
    changeSelectionOfTransportByName,
    chooseTransportPersonalCollectionAndStore,
} from './transportAndPaymentSupport';
import { payment, transport, url } from 'fixtures/demodata';
import { generateCreateOrderInput, generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.TRANSPORT_AND_PAYMENT, SUBGROUP_INDEX);

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

    it('[Preselect T&P] should preselect transport and payment from last order for logged-in user', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'preselected transport and payment', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Change T&P And Preserve On Refresh] should change preselected transport and payment from last order for logged-in user and keep the new selection after refresh', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(1000);
        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(1000);
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(1000);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after first change and refresh', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(1000);
        chooseTransportPersonalCollectionAndStore(transport.personalCollection.storePardubice.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(1000);
        changeSelectionOfPaymentByName(payment.cash.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(1000);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after second change and refresh', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
