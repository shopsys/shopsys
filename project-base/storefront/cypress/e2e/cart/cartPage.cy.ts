import {
    increaseCartItemQuantityWithSpinbox,
    decreaseCartItemQuantityWithSpinbox,
    goToNextOrderStep,
    goToPreviousOrderStep,
    removeProductFromCartPage,
    applyPromoCodeOnCartPage,
    removePromoCodeOnCartPage,
    clickOnPromoCodeButton,
    checkCartItemSpinboxDecreaseButtonIsDisabled,
    checkCartItemSpinboxIncreaseButtonIsEnabled,
    checkCartItemSpinboxDecreaseButtonIsEnabled,
} from './cartSupport';
import { checkTransportSelectionIsVisible } from 'e2e/order/orderSupport';
import { changeSelectionOfTransportByName } from 'e2e/transportAndPayment/transportAndPaymentSupport';
import { products, transport, url } from 'fixtures/demodata';
import {
    changeCartItemQuantityWithSpinboxInput,
    checkAndHideInfoToast,
    checkAndHideSuccessToast,
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    checkNumberOfApiRequestsTriggeredByActions,
    checkUrl,
    getSnapshotIndexingFunction,
    getTestSummary,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 2;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);

describe('Cart Page Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.addProductToCartForTest(products.helloKitty.uuid, 2).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
    });

    it('[Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast)', function () {
        const testSummary = getTestSummary(this.test?.title);
        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                cy.wait(1100);
            },
            1,
            'AddToCartMutation',
        );
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after increase', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                cy.wait(1100);
            },
            1,
            'AddToCartMutation',
        );
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after decrease', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly)', function () {
        const testSummary = getTestSummary(this.test?.title);
        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(1100);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(1100);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(1100);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(1100);
            },
            4,
            'AddToCartMutation',
        );
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after increase', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(1100);
                decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(1100);
            },
            2,
            'AddToCartMutation',
        );
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after decrease', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove Products] should remove products from cart', function () {
        const testSummary = getTestSummary(this.test?.title);
        removeProductFromCartPage(products.philips32PFL4308.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after first removal', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeProductFromCartPage(products.helloKitty.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'empty cart after second removal', {
            blackout: [{ tid: TIDs.footer_social_links }, { tid: TIDs.footer_copyright }],
        });
    });

    it('[Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity', function () {
        checkCartItemSpinboxDecreaseButtonIsDisabled(products.philips32PFL4308.catnum);
        changeCartItemQuantityWithSpinboxInput(10000, products.philips32PFL4308.catnum);
        checkCartItemSpinboxDecreaseButtonIsEnabled(products.philips32PFL4308.catnum);
    });

    it('[Quantity Spinbox Increase] max spinbox button should be always clickable', function () {
        checkCartItemSpinboxIncreaseButtonIsEnabled(products.philips32PFL4308.catnum);
        changeCartItemQuantityWithSpinboxInput(10000, products.philips32PFL4308.catnum);
        checkCartItemSpinboxIncreaseButtonIsEnabled(products.philips32PFL4308.catnum);
    });

    it('[Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one', function () {
        const testSummary = getTestSummary(this.test?.title);
        clickOnPromoCodeButton();
        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'cart page after applying first promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToNextOrderStep();
        checkUrl(url.order.transportAndPayment);
        checkTransportSelectionIsVisible();
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(testSummary),
            'transport and payment page after applying first promocode',
            {
                blackout: [
                    { tid: TIDs.order_summary_cart_item_image },
                    { tid: TIDs.transport_and_payment_list_item_image },
                ],
            },
        );

        goToPreviousOrderStep();
        checkUrl(url.cart);
        removePromoCodeOnCartPage();
        checkAndHideSuccessToast('Promo code was removed from the order.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'cart page after removing first promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });

        applyPromoCodeOnCartPage('test-product2');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'cart page after removing second promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Add Promo Remove Product] should add promo code to cart, remove product that allows it, and see the promo code removed', function () {
        const testSummary = getTestSummary(this.test?.title);
        clickOnPromoCodeButton();

        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'after applying promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeProductFromCartPage(products.helloKitty.catnum);
        checkAndHideInfoToast('The promo code test is no longer applicable.');
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(testSummary),
            'after removing product that allows promocode',
            {
                blackout: [
                    { tid: TIDs.cart_list_item_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_copyright },
                ],
            },
        );
    });

    it('[No Free Transport] transport should not be free if price minus promo code discount is below the free transport limit', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.addProductToCartForTest(products.helloKitty.uuid, 10);
        cy.reloadAndWaitForStableAndInteractiveDOM();

        clickOnPromoCodeButton();
        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(testSummary),
            'cart page with non-free transport after applying promocode',
            {
                blackout: [
                    { tid: TIDs.cart_list_item_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_copyright },
                ],
            },
        );

        goToNextOrderStep();
        changeSelectionOfTransportByName(transport.ppl.name);
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(testSummary),
            'transport and payment page with non-free options after applying promocode',
            {
                blackout: [
                    { tid: TIDs.order_summary_cart_item_image },
                    { tid: TIDs.order_summary_transport_and_payment_image },
                    { tid: TIDs.transport_and_payment_list_item_image },
                ],
            },
        );
    });
});
