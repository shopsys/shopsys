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
    getSnapshotIndexingFunction,
    getTestSummary,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 2;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.TRANSPORT_AND_PAYMENT, SUBGROUP_INDEX);

describe('Transport Select Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Transport Home] should select transport to home', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });

    it('[Personal Collection] should select personal pickup transport', function () {
        const testSummary = getTestSummary(this.test?.title);
        changeDayOfWeekInTransportsApiResponse(1);
        changeDayOfWeekInChangeTransportMutationResponse(1);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        chooseTransportPersonalCollectionAndStore(transport.personalCollection.storeOstrava.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });

    it('[Change Transport] should select a transport, deselect it, and then change the transport option', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(testSummary),
            'after selecting, deselecting, and selecting again',
            {
                blackout: [
                    { tid: TIDs.transport_and_payment_list_item_image },
                    { tid: TIDs.order_summary_cart_item_image },
                    { tid: TIDs.order_summary_transport_and_payment_image },
                ],
            },
        );
    });

    it('[Remove Transport Repeated Click] should be able to remove transport using repeated clicks', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });

    it('[Remove Transport Button Click] should remove transport using reset button', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(transport.czechPost.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        removeTransportSelectionUsingButton();
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
            ],
        });
    });

    it('[Anon No Transport Empty Cart] should redirect to cart page and not display transport options if cart is empty and user is not logged in', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        checkTransportSelectionIsNotVisible();
        checkEmptyCartTextIsVisible();
        checkUrl(url.cart);
        checkEmptyCartTextIsVisible();
    });

    it(
        '[Logged No Transport Empty Cart] should redirect to cart page and not display transport options if cart is empty and user is logged in',
        { retries: { runMode: 0 } },
        function () {
            cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer'));
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

            checkTransportSelectionIsNotVisible();
            checkEmptyCartTextIsVisible();
            checkUrl(url.cart);
            checkEmptyCartTextIsVisible();
        },
    );

    it('[Transport Fee] should change price for transport when cart is large enough for transport to be free', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(testSummary),
            'transport and payment page with too few products',
            {
                blackout: [
                    { tid: TIDs.transport_and_payment_list_item_image },
                    { tid: TIDs.order_summary_cart_item_image },
                ],
            },
        );

        cy.addProductToCartForTest(products.helloKitty.uuid, 1099);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'cart page with enough products', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToNextOrderStep();
        changeSelectionOfTransportByName(transport.ppl.name);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(testSummary),
            'transport and payment page with enough products',
            {
                blackout: [
                    { tid: TIDs.transport_and_payment_list_item_image },
                    { tid: TIDs.order_summary_cart_item_image },
                ],
            },
        );
    });
});
