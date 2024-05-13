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
    checkCartItemSpinboxIncreaseButtonIsDisabled,
} from './cartSupport';
import { checkTransportSelectionIsVisible } from 'e2e/order/orderSupport';
import { changeSelectionOfTransportByName } from 'e2e/transportAndPayment/transportAndPaymentSupport';
import { DEFAULT_APP_STORE, products, transport, url } from 'fixtures/demodata';
import {
    changeCartItemQuantityWithSpinboxInput,
    checkAndHideInfoToast,
    checkAndHideSuccessToast,
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    checkNumberOfApiRequestsTriggeredByActions,
    checkUrl,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Cart page tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
        cy.addProductToCartForTest(products.helloKitty.uuid, 2).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
    });

    it('should increase and decrease product quantity using spinbox in cart (once if clicked fast)', function () {
        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                cy.wait(500);
            },
            1,
            'AddToCartMutation',
        );
        takeSnapshotAndCompare(this.test?.title, 'after increase', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                cy.wait(500);
            },
            1,
            'AddToCartMutation',
        );
        takeSnapshotAndCompare(this.test?.title, 'after decrease', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });
    });

    it('should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly)', function () {
        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(600);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(600);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(600);
                increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(600);
            },
            4,
            'AddToCartMutation',
        );
        takeSnapshotAndCompare(this.test?.title, 'after increase', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(600);
                decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
                checkLoaderOverlayIsNotVisibleAfterTimePeriod(600);
            },
            2,
            'AddToCartMutation',
        );
        takeSnapshotAndCompare(this.test?.title, 'after decrease', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });
    });

    it('should remove products from cart', function () {
        removeProductFromCartPage(products.philips32PFL4308.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'after first removal', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });

        removeProductFromCartPage(products.helloKitty.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(this.test?.title, 'empty cart after second removal');
    });

    it('spinbox buttons should not be clickable if they cannot be used due to min/max quantity', function () {
        checkCartItemSpinboxDecreaseButtonIsDisabled(products.philips32PFL4308.catnum);
        checkCartItemSpinboxIncreaseButtonIsEnabled(products.philips32PFL4308.catnum);

        changeCartItemQuantityWithSpinboxInput(10000, products.philips32PFL4308.catnum);

        checkCartItemSpinboxDecreaseButtonIsEnabled(products.philips32PFL4308.catnum);
        checkCartItemSpinboxIncreaseButtonIsDisabled(products.philips32PFL4308.catnum);
    });

    it('should add promo code to cart, check it, remove promo code from cart, and then add a different one', function () {
        clickOnPromoCodeButton();
        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(this.test?.title, 'cart page after applying first promocode', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });

        goToNextOrderStep();
        checkUrl(url.order.transportAndPayment);
        checkTransportSelectionIsVisible();
        takeSnapshotAndCompare(this.test?.title, 'transport and payment page after applying first promocode', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
            ],
        });

        goToPreviousOrderStep();
        checkUrl(url.cart);
        removePromoCodeOnCartPage();
        checkAndHideSuccessToast('Promo code was removed from the order.');
        takeSnapshotAndCompare(this.test?.title, 'cart page after removing first promocode', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });

        applyPromoCodeOnCartPage('test-product2');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(this.test?.title, 'cart page after removing second promocode', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });
    });

    it('should add promo code to cart, remove product that allows it, and see the promo code removed', function () {
        clickOnPromoCodeButton();

        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(this.test?.title, 'after applying promocode', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });

        removeProductFromCartPage(products.helloKitty.catnum);
        checkAndHideInfoToast('The promo code test is no longer applicable.');
        takeSnapshotAndCompare(this.test?.title, 'after removing product that allows promocode', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });
    });

    it('transport should not be free if price minus promo code discount is below the free transport limit', function () {
        cy.addProductToCartForTest(products.helloKitty.uuid, 70);
        cy.reloadAndWaitForStableAndInteractiveDOM();

        clickOnPromoCodeButton();
        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(this.test?.title, 'cart page with non-free transport after applying promocode', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }],
        });

        goToNextOrderStep();
        changeSelectionOfTransportByName(transport.ppl.name);
        takeSnapshotAndCompare(
            this.test?.title,
            'transport and payment page with non-free options after applying promocode',
            {
                blackout: [
                    { tid: TIDs.order_summary_cart_item_image },
                    { tid: TIDs.order_summary_transport_and_payment_image },
                    { tid: TIDs.transport_and_payment_list_item_image, shouldNotOffset: true },
                ],
            },
        );
    });
});
