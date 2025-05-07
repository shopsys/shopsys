import {
    changeSelectionOfPaymentByName,
    changeSelectionOfTransportByName,
    removePaymentSelectionUsingButton,
    removeTransportSelectionUsingButton,
} from './transportAndPaymentSupport';
import { goToNextOrderStep } from 'e2e/cart/cartSupport';
import { payment, transport, url } from 'fixtures/demodata';
import {
    checkCanGoToNextOrderStep,
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    checkUrl,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 1;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.TRANSPORT_AND_PAYMENT, SUBGROUP_INDEX);

describe('Payment Select Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);
    });

    it('[Select Payment] should select payment on delivery', function () {
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        checkCanGoToNextOrderStep();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after payment selection', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToNextOrderStep();
        checkUrl(url.order.contactInformation);
    });

    it('[Select And Change Payment] should select a payment, deselect it, and then change the payment option', function () {
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        checkCanGoToNextOrderStep();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after changing payment selection', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToNextOrderStep();
        checkUrl(url.order.contactInformation);
    });

    it('[Remove Payment Repeated Click] should remove payment using repeated clicks', function () {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove Payment Button Click] should remove payment using reset button', function () {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        removePaymentSelectionUsingButton();
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove & Select New T&P] should remove transport to remove payment as well, and then allow to select transport incompatible with previous payment', function () {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeTransportSelectionUsingButton();
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after removing transport', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(),
            'after selecting transport incompatible with the previous payment',
            {
                blackout: [
                    { tid: TIDs.transport_and_payment_list_item_image },
                    { tid: TIDs.order_summary_cart_item_image },
                    { tid: TIDs.footer_copyright },
                ],
            },
        );
    });
});
