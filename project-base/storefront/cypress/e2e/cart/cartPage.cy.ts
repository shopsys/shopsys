import {
    applyPromoCodeOnCartPage,
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
import { staticData, url } from 'fixtures/demodata';
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
    translations,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 2;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);
type DataLayerWindow = Cypress.AUTWindow & {
    dataLayer?: Array<{ ecommerce?: { products?: Array<{ quantity?: number }> }; event?: string }>;
};

describe('Cart Page Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.addProductToCartForTest(staticData.products.helloKitty.uuid, 2).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(staticData.products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
    });

    it('[Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast)', function () {
        cy.intercept('POST', '/graphql/AddToCartMutation').as('addToCartMutation');

        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
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

        decreaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        decreaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
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

    it('[Immediate Quantity Reversal] should submit both cart changes and send matching GTM events', () => {
        cy.intercept('POST', '/graphql/AddToCartMutation').as('addToCartMutation');
        cy.window().then((window) => {
            (window as DataLayerWindow).dataLayer = [];
        });

        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        cy.wait('@addToCartMutation').then(({ request }) => {
            expect(request.body.variables.input).to.include({
                isAbsoluteQuantity: true,
                quantity: 3,
            });
        });
        cy.getByTID([TIDs.loader_overlay]).should('not.exist');

        decreaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        cy.wait('@addToCartMutation').then(({ request }) => {
            expect(request.body.variables.input).to.include({
                isAbsoluteQuantity: true,
                quantity: 2,
            });
        });

        cy.window().should((window) => {
            const cartChangeEvents = (window as DataLayerWindow).dataLayer
                ?.filter(({ event }) => event === 'ec.add_to_cart' || event === 'ec.remove_from_cart')
                .map(({ ecommerce, event }) => ({ event, quantity: ecommerce?.products?.[0]?.quantity }));

            expect(cartChangeEvents).to.deep.equal([
                { event: 'ec.add_to_cart', quantity: 1 },
                { event: 'ec.remove_from_cart', quantity: 1 },
            ]);
        });
    });

    it('[Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly)', function () {
        cy.intercept('POST', '/graphql/AddToCartMutation').as('addToCartMutation');

        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        increaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
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

        decreaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
        cy.wait('@addToCartMutation');
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(300);

        decreaseCartItemQuantityWithSpinbox(staticData.products.helloKitty.catnum);
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
        removeProductFromCartPage(staticData.products.philips32PFL4308.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after first removal', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeProductFromCartPage(staticData.products.helloKitty.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'empty cart after second removal', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Quantity Spinbox Decrease] min spinbox button should stay clickable for removing the cart item', function () {
        checkCartItemSpinboxDecreaseButtonIsEnabled(staticData.products.philips32PFL4308.catnum);
        cy.getByTID([[TIDs.pages_cart_list_item_, staticData.products.philips32PFL4308.catnum], TIDs.spinbox_input])
            .clear()
            .type('50')
            .trigger('input')
            .blur();
        cy.wait(200);
        checkCartItemSpinboxDecreaseButtonIsEnabled(staticData.products.philips32PFL4308.catnum);
    });

    it('[Quantity Spinbox Increase] max spinbox button should be always clickable', function () {
        checkCartItemSpinboxIncreaseButtonIsEnabled(staticData.products.philips32PFL4308.catnum);
        cy.getByTID([[TIDs.pages_cart_list_item_, staticData.products.philips32PFL4308.catnum], TIDs.spinbox_input])
            .clear()
            .type('50')
            .trigger('input')
            .blur();
        cy.wait(200);
        checkCartItemSpinboxIncreaseButtonIsEnabled(staticData.products.philips32PFL4308.catnum);
    });

    it('[Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one', function () {
        clickOnPromoCodeButton();
        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast(translations.toast.success.promoCodeAdded);
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
        checkAndHideSuccessToast(translations.toast.success.promoCodeRemoved);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after removing first promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        applyPromoCodeOnCartPage('test-product2');
        checkAndHideSuccessToast(translations.toast.success.promoCodeAdded);
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
        checkAndHideSuccessToast(translations.toast.success.promoCodeAdded);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after applying promocode', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeProductFromCartPage(staticData.products.helloKitty.catnum);
        checkAndHideInfoToast(translations.toast.info.promoCodeNotApplicable.replace('{{ promoCode }}', 'test'));
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
        cy.addProductToCartForTest(staticData.products.helloKitty.uuid, 10);
        cy.reloadAndWaitForStableAndInteractiveDOM();

        clickOnPromoCodeButton();
        applyPromoCodeOnCartPage('test');
        checkAndHideSuccessToast(translations.toast.success.promoCodeAdded);
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
        changeSelectionOfTransportByName(translations.transport.ppl, translations.transportGroup.deliveryToAddress);
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
