import {
    increaseCartItemQuantityWithSpinbox,
    checkCartItemTotalPrice,
    checkCartTotalPrice,
    decreaseCartItemQuantityWithSpinbox,
    continueToTransportAndPaymentSelection,
} from './cartSupport';
import { products, url } from 'fixtures/demodata';
import { checkLoaderOverlayIsNotVisible, checkUrl } from 'support';

describe('Cart page tests', () => {
    beforeEach(() => {
        cy.addProductToCartForTest(undefined, 2).then((cartUuid) => cy.storeCartUuidInLocalStorage(cartUuid));
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visit(url.cart);
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
        checkCartItemTotalPrice('€1,978.00');
        checkCartTotalPrice('€2,257.92');

        decreaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible();

        decreaseCartItemQuantityWithSpinbox();
        checkLoaderOverlayIsNotVisible(300);
        checkCartItemTotalPrice('€1,186.80');
        checkCartTotalPrice('€1,466.72');

        continueToTransportAndPaymentSelection();
        checkUrl(url.order.transportAndPayment);
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
        cy.getByDataTestId(['pages-cart-list-item-0', 'pages-cart-removecartitembutton']).click();
        cy.getByDataTestId(['pages-cart-list-item-0', 'pages-cart-list-item-name']).contains(
            products.helloKitty.fullName,
        );
        cy.getByDataTestId('pages-cart-list-item-1').should('not.exist');
        checkCartTotalPrice('€279.92');

        cy.getByDataTestId(['pages-cart-list-item-0', 'pages-cart-removecartitembutton']).click();
        cy.getByDataTestId('pages-cart-list-item-0').should('not.exist');
        cy.getByDataTestId('cart-page-empty-cart-text').should('be.visible');
    });

    it('should add and then remove promo code from cart', () => {
        cy.getByDataTestId('blocks-promocode-add-button').click();
        cy.get('#blocks-promocode-input').should('be.visible').type('test', { force: true });
        cy.getByDataTestId('blocks-promocode-apply-button').click();
        cy.getByDataTestId('blocks-promocode-promocodeinfo-code').contains('test');
        cy.getByDataTestId('pages-cart-cartpreview-discount').contains('-€27.99');
        checkCartTotalPrice('€647.53');

        continueToTransportAndPaymentSelection();
        checkUrl(url.order.transportAndPayment);
    });
});
