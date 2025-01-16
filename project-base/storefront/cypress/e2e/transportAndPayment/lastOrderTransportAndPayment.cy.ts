import {
    changeOpeningHoursDayOfWeekWithDateToStaticString,
    changeOpeningHoursRangesToStaticString,
    changeOpeningHoursStatusToEmptyString,
    changeSelectionOfPaymentByName,
    changeSelectionOfTransportByName,
    chooseTransportPersonalCollectionAndStore,
} from './transportAndPaymentSupport';
import { payment, transport, url } from 'fixtures/demodata';
import { generateCreateOrderInput, generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    getSnapshotIndexingFunction,
    getTestSummary,
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
        const testSummary = getTestSummary(this.test?.title);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'preselected transport and payment', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Change T&P And Preserve On Refresh] should change preselected transport and payment from last order for logged-in user and keep the new selection after refresh', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after first change and refresh', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        chooseTransportPersonalCollectionAndStore(transport.personalCollection.storePardubice.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        changeSelectionOfPaymentByName(payment.cash.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(500);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        changeOpeningHoursDayOfWeekWithDateToStaticString('Wednesday 30.10.2024');
        changeOpeningHoursStatusToEmptyString();
        changeOpeningHoursRangesToStaticString('8:00 - 18:00');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after second change and refresh', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
                { tid: TIDs.opening_hours },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
