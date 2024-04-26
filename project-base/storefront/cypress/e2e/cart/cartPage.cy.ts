import {
    increaseCartItemQuantityWithSpinbox,
    decreaseCartItemQuantityWithSpinbox,
    continueToTransportAndPaymentSelection,
    goBackToCartPage,
} from './cartSupport';
import { DEFAULT_APP_STORE, products, url } from 'fixtures/demodata';
import {
    checkAndHideInfoToast,
    checkAndHideSuccessToast,
    checkLoaderOverlayIsNotVisible,
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
        cy.visitAndWaitForStableDOM(url.cart);
    });

    it('should increase and decrease product quantity using spinbox in cart (once if clicked fast)', () => {
        increaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible();

        increaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible();

        increaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible();

        increaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        takeSnapshotAndCompare('increase-and-decrease-cart-item-quantity-using-fast-spinbox-click_increase');

        decreaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible();

        decreaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        takeSnapshotAndCompare('increase-and-decrease-cart-item-quantity-using-fast-spinbox-click_decrease');
    });

    it('should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly)', () => {
        increaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        takeSnapshotAndCompare('increase-and-decrease-cart-item-quantity-using-slow-spinbox-click_first_increase');

        increaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        takeSnapshotAndCompare('increase-and-decrease-cart-item-quantity-using-slow-spinbox-click_second_increase');

        decreaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        takeSnapshotAndCompare('increase-and-decrease-cart-item-quantity-using-slow-spinbox-click_decrease');
    });

    it('should remove products from cart', () => {
        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.pages_cart_removecartitembutton]).click();
        takeSnapshotAndCompare('remove-products-from-cart_last_product_left');

        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.pages_cart_removecartitembutton]).click();
        takeSnapshotAndCompare('remove-products-from-cart_empty_cart');
    });

    it('spinbox buttons should not be clickable if they cannot be used due to min/max quantity', () => {
        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.forms_spinbox_decrease]).should(
            'have.css',
            'pointer-events',
            'none',
        );
        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.forms_spinbox_increase]).should(
            'not.have.css',
            'pointer-events',
            'none',
        );

        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.spinbox_input]).type('10000');

        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.forms_spinbox_decrease]).should(
            'not.have.css',
            'pointer-events',
            'none',
        );
        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.forms_spinbox_increase]).should(
            'have.css',
            'pointer-events',
            'none',
        );
    });

    it('should add promo code to cart, check it, remove promo code from cart, and then add a different one', () => {
        cy.getByTID([TIDs.blocks_promocode_add_button]).click();
        cy.get('#blocks-promocode-input').should('be.visible').type('test', { force: true });
        cy.getByTID([TIDs.blocks_promocode_apply_button]).click();
        checkAndHideSuccessToast('Promo code was added to the order.');

        takeSnapshotAndCompare('apply-remove-and-apply-promocode_after-applying-first-promocode');

        continueToTransportAndPaymentSelection();
        checkUrl(url.order.transportAndPayment);
        takeSnapshotAndCompare('transport-and-payment-page-with-applied-promo-code');

        goBackToCartPage();
        checkUrl(url.cart);
        cy.getByTID([TIDs.blocks_promocode_promocodeinfo_code]).find('svg').click();
        checkAndHideSuccessToast('Promo code was removed from the order.');

        takeSnapshotAndCompare('apply-remove-and-apply-promocode_after-removing-first-promocode');

        cy.get('#blocks-promocode-input').should('be.visible').clear().type('test-product2', { force: true });
        cy.getByTID([TIDs.blocks_promocode_apply_button]).click();
        checkAndHideSuccessToast('Promo code was added to the order.');

        takeSnapshotAndCompare('apply-remove-and-apply-promocode_after-applying-second-promocode');
    });

    it('should add promo code to cart, remove product that allows it see the promo code removed', () => {
        cy.getByTID([TIDs.blocks_promocode_add_button]).click();
        cy.get('#blocks-promocode-input').should('be.visible').type('test', { force: true });
        cy.getByTID([TIDs.blocks_promocode_apply_button]).click();
        checkAndHideSuccessToast('Promo code was added to the order.');

        takeSnapshotAndCompare('apply-promocode-and-remove-necessary-product_after-applying');

        cy.getByTID([[TIDs.pages_cart_list_item_, 1], TIDs.pages_cart_removecartitembutton]).click();
        checkAndHideInfoToast('The promo code test is no longer applicable.');
    });

    it('transport should not be free if price minus promo code discount is below the free transport limit', () => {
        cy.addProductToCartForTest(products.helloKitty.uuid, 70).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.reloadAndWaitForStableDOM();

        cy.getByTID([TIDs.blocks_promocode_add_button]).click();
        cy.get('#blocks-promocode-input').should('be.visible').type('test', { force: true });
        cy.getByTID([TIDs.blocks_promocode_apply_button]).click();
        checkAndHideSuccessToast('Promo code was added to the order.');

        takeSnapshotAndCompare('no-free-transport-with-discounted-price-below-limit_cart-page');

        continueToTransportAndPaymentSelection();

        takeSnapshotAndCompare('no-free-transport-with-discounted-price-below-limit_transport-and-payment-page');
    });
});
