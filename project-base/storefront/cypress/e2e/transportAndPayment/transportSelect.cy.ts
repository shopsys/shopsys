import {
    changeDayOfWeekInChangeTransportMutationResponse,
    changeDayOfWeekInTransportsApiResponse,
    changeSelectionOfTransportByName,
    chooseTransportPersonalCollectionAndStore,
    removeTransportSelectionUsingButton,
} from './transportAndPaymentSupport';
import { goToNextOrderStep } from 'e2e/cart/cartSupport';
import { checkEmptyCartTextIsVisible, checkTransportSelectionIsNotVisible } from 'e2e/order/orderSupport';
import { products, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    checkUrl,
    initializePersistStoreInLocalStorageToDefaultValues,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Transport select tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should select transport to home', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });

    it('should select personal pickup transport', function () {
        changeDayOfWeekInTransportsApiResponse(1);
        changeDayOfWeekInChangeTransportMutationResponse(1);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        chooseTransportPersonalCollectionAndStore(transport.personalCollection.storeOstrava.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });

    it('should select a transport, deselect it, and then change the transport option', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after selecting, deselecting, and selecting again', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });

    it('should be able to remove transport using repeated clicks', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });

    it('should be able to remove transport using reset button', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
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
        takeSnapshotAndCompare(this.test?.title, 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });

    it('should redirect to cart page and not display transport options if cart is empty and user is not logged in', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        checkTransportSelectionIsNotVisible();
        checkEmptyCartTextIsVisible();
        checkUrl(url.cart);
        takeSnapshotAndCompare(this.test?.title, 'after redirecting to cart page', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });
    });

    it('should redirect to cart page and not display transport options if cart is empty and user is logged in', function () {
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        checkTransportSelectionIsNotVisible();
        checkEmptyCartTextIsVisible();
        checkUrl(url.cart);
        takeSnapshotAndCompare(this.test?.title, 'after redirecting to cart page', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });
    });

    it('should change price for transport when cart is large enough for transport to be free', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        takeSnapshotAndCompare(this.test?.title, 'transport and payment page with too few products', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });

        cy.addProductToCartForTest(products.helloKitty.uuid, 1099);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
        takeSnapshotAndCompare(this.test?.title, 'cart page with enough products', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }, { tid: TIDs.footer_social_links }],
        });

        goToNextOrderStep();
        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'transport and payment page with enough products', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });
});
