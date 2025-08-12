import {
    applyPromoCodeOnCartPage,
    checkCartItemSpinboxDecreaseButtonIsDisabled,
    checkCartItemSpinboxDecreaseButtonIsEnabled,
    checkCartItemSpinboxIncreaseButtonIsEnabled,
    clickOnPromoCodeButton,
    decreaseCartItemQuantityWithSpinbox,
    goToNextOrderStep,
    goToPreviousOrderStep,
    increaseCartItemQuantityWithSpinbox,
    removeProductFromCartPage,
    removePromoCodeOnCartPage,
} from './cartSupport';
import { checkTransportSelectionIsVisible } from 'e2e/order/orderSupport';
import { changeSelectionOfTransportByName } from 'e2e/transportAndPayment/transportAndPaymentSupport';
import { products, transport, url } from 'fixtures/demodata';
import {
    checkAndHideInfoToast,
    checkAndHideSuccessToast,
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    checkUrl,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
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
        cy.intercept('POST', '/graphql/AddToCartMutation').as('addToCartMutation');

        increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        loseFocus();

        cy.wait('@addToCartMutation');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after increase', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        loseFocus();

        cy.wait('@addToCartMutation');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after decrease', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly)', function () {
        cy.intercept('POST', '/graphql/AddToCartMutation').as('addToCartMutation');

        increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        increaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after increase', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        decreaseCartItemQuantityWithSpinbox(products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after decrease', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove Products] should remove products from cart', function () {
        removeProductFromCartPage(products.philips32PFL4308.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after first removal', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeProductFromCartPage(products.helloKitty.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'empty cart after second removal', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Quantity Spinbox Decrease] min spinbox button should not be clickable if it cannot be used due to min quantity', function () {
        checkCartItemSpinboxDecreaseButtonIsDisabled(products.philips32PFL4308.catnum);
        cy.getByTID([[TIDs.pages_cart_list_item_, products.philips32PFL4308.catnum], TIDs.spinbox_input])
            .clear()
            .type('50')
            .trigger('input')
            .blur();
        cy.wait(200);
        checkCartItemSpinboxDecreaseButtonIsEnabled(products.philips32PFL4308.catnum);
    });

    it('[Quantity Spinbox Increase] max spinbox button should be always clickable', function () {
        checkCartItemSpinboxIncreaseButtonIsEnabled(products.philips32PFL4308.catnum);
        cy.getByTID([[TIDs.pages_cart_list_item_, products.philips32PFL4308.catnum], TIDs.spinbox_input])
            .clear()
            .type('50')
            .trigger('input')
            .blur();
        cy.wait(200);
        checkCartItemSpinboxIncreaseButtonIsEnabled(products.philips32PFL4308.catnum);
    });

    it('[Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one', function () {
        clickOnPromoCodeButton();
        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after applying first promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToNextOrderStep();
        checkUrl(url.order.transportAndPayment);
        checkTransportSelectionIsVisible();
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(),
            'transport and payment page after applying first promocode',
            {
                blackout: [
                    { tid: TIDs.order_summary_cart_item_image },
                    { tid: TIDs.transport_and_payment_list_item_image },
                    { tid: TIDs.footer_copyright },
                ],
            },
        );

        goToPreviousOrderStep();
        checkUrl(url.cart);
        removePromoCodeOnCartPage();
        checkAndHideSuccessToast('Promo code was removed from the order.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after removing first promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        applyPromoCodeOnCartPage('test-product2');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after removing second promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Add Promo Remove Product] should add promo code to cart, remove product that allows it, and see the promo code removed', function () {
        clickOnPromoCodeButton();

        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after applying promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeProductFromCartPage(products.helloKitty.catnum);
        checkAndHideInfoToast('The promo code test is no longer applicable.');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after removing product that allows promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[No Free Transport] transport should not be free if price minus promo code discount is below the free transport limit', function () {
        cy.addProductToCartForTest(products.helloKitty.uuid, 10);
        cy.reloadAndWaitForStableAndInteractiveDOM();

        clickOnPromoCodeButton();
        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast('Promo code was added to the order.');
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(),
            'cart page with non-free transport after applying promocode',
            {
                blackout: [
                    { tid: TIDs.cart_list_item_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            },
        );

        goToNextOrderStep();
        changeSelectionOfTransportByName(transport.ppl.name);
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(),
            'transport and payment page with non-free options after applying promocode',
            {
                blackout: [
                    { tid: TIDs.order_summary_cart_item_image },
                    { tid: TIDs.transport_and_payment_list_item_image },
                    { tid: TIDs.footer_copyright },
                ],
            },
        );
    });
});
