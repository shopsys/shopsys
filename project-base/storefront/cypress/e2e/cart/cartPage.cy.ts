import {
    increaseCartItemQuantityWithSpinbox,
    checkCartItemTotalPrice,
    checkCartTotalPrice,
    decreaseCartItemQuantityWithSpinbox,
    continueToTransportAndPaymentSelection,
} from './cartSupport';
import { products, url } from 'fixtures/demodata';
import { checkLoaderOverlayIsNotVisible, checkUrl } from 'support';
import { TIDs } from 'tids';

describe('Cart page tests', () => {
    beforeEach(() => {
        cy.addProductToCartForTest(undefined, 2).then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableDOM(url.cart);
    });

    it('should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly)', () => {
        increaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        checkCartItemTotalPrice('€791.20');
        checkCartTotalPrice('€1,071.12');

        increaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        checkCartItemTotalPrice('€1,186.80');
        checkCartTotalPrice('€1,466.72');

        decreaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        checkCartItemTotalPrice('€791.20');
        checkCartTotalPrice('€1,071.12');

        continueToTransportAndPaymentSelection();
        checkUrl(url.order.transportAndPayment);
    });

    it('should remove products from cart', () => {
        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.pages_cart_removecartitembutton]).click();
        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.pages_cart_list_item_name]).contains(
            products.helloKitty.fullName,
        );
        cy.getByTID([[TIDs.pages_cart_list_item_, 1]]).should('not.exist');
        checkCartTotalPrice('€279.92');

        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.pages_cart_removecartitembutton]).click();
        cy.getByTID([[TIDs.pages_cart_list_item_, 0]]).should('not.exist');
        cy.getByTID([TIDs.cart_page_empty_cart_text]).should('be.visible');
    });

    it('should add and then remove promo code from cart', () => {
        cy.getByTID([TIDs.blocks_promocode_add_button]).click();
        cy.get('#blocks-promocode-input').should('be.visible').type('test', { force: true });
        cy.getByTID([TIDs.blocks_promocode_apply_button]).click();
        cy.getByTID([TIDs.blocks_promocode_promocodeinfo_code]).contains('test');
        cy.getByTID([TIDs.pages_cart_cartpreview_discount]).contains('-€27.99');
        checkCartTotalPrice('€647.53');

        continueToTransportAndPaymentSelection();
        checkUrl(url.order.transportAndPayment);
    });
});
