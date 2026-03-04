import {
    changeDayOfWeekInChangeTransportMutationResponse,
    changeDayOfWeekInTransportsApiResponse,
    changeSelectionOfTransportByName,
    chooseTransportPersonalCollectionAndStore,
    removeTransportSelectionUsingButton,
    waitForTransportAndPaymentToBeInteractive,
} from './transportAndPaymentSupport';
import { goToNextOrderStep } from 'e2e/cart/cartSupport';
import { checkEmptyCartTextIsVisible, checkTransportSelectionIsNotVisible } from 'e2e/order/orderSupport';
import { staticData, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkUrl,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
    translations,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 2;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.TRANSPORT_AND_PAYMENT, SUBGROUP_INDEX);

describe('Transport Select Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Transport Home] should select transport to home', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(translations.transport.czechPost);
        waitForTransportAndPaymentToBeInteractive();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Personal Collection] should select personal pickup transport', function () {
        changeDayOfWeekInTransportsApiResponse(1);
        changeDayOfWeekInChangeTransportMutationResponse(1);
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        chooseTransportPersonalCollectionAndStore(
            staticData.transport.personalCollection.storeOstrava.name,
            translations.transport.personalCollection,
        );
        waitForTransportAndPaymentToBeInteractive();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Change Transport] should select a transport, deselect it, and then change the transport option', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(translations.transport.czechPost);
        waitForTransportAndPaymentToBeInteractive();
        changeSelectionOfTransportByName(translations.transport.czechPost);
        waitForTransportAndPaymentToBeInteractive();
        changeSelectionOfTransportByName(translations.transport.ppl);
        waitForTransportAndPaymentToBeInteractive();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after selecting, deselecting, and selecting again', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove Transport Repeated Click] should be able to remove transport using repeated clicks', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(translations.transport.czechPost);
        waitForTransportAndPaymentToBeInteractive();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        changeSelectionOfTransportByName(translations.transport.czechPost);
        waitForTransportAndPaymentToBeInteractive();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove Transport Button Click] should remove transport using reset button', function () {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(translations.transport.czechPost);
        waitForTransportAndPaymentToBeInteractive();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after selecting', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeTransportSelectionUsingButton();
        waitForTransportAndPaymentToBeInteractive();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after removing', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
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
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'transport and payment page with too few products', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        cy.addProductToCartForTest(staticData.products.helloKitty.uuid, 998);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page with enough products', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToNextOrderStep();
        changeSelectionOfTransportByName(translations.transport.ppl);
        waitForTransportAndPaymentToBeInteractive();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'transport and payment page with enough products', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
