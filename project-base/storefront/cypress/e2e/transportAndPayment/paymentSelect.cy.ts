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
    initializePersistStoreInLocalStorageToDefaultValues,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Payment select tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);
    });

    it('should select payment on delivery', function () {
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        checkCanGoToNextOrderStep();
        takeSnapshotAndCompare(this.test?.title, 'after payment selection', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        goToNextOrderStep();
        checkUrl(url.order.contactInformation);
    });

    it('should select a payment, deselect it, and then change the payment option', function () {
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        changeSelectionOfPaymentByName(payment.onDelivery.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        checkCanGoToNextOrderStep();
        takeSnapshotAndCompare(this.test?.title, 'after changing payment selection', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        goToNextOrderStep();
        checkUrl(url.order.contactInformation);
    });

    it('should be able to remove payment using repeated clicks', function () {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });

    it('should be able to remove payment using reset button', function () {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        removePaymentSelectionUsingButton();
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });

    it('removing transport should remove payment as well, and then allow to select transport incompatible with previous payment', function () {
        changeSelectionOfPaymentByName(payment.creditCard.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        removeTransportSelectionUsingButton();
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after removing transport', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
            ],
        });

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after selecting transport incompatible with the previous payment', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });
});
