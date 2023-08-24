import { products, totalPrice, url } from 'fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from 'integration/Functions/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from 'integration/Functions/cartPopupWindow';
import { addProductToCartFromPromotedProductsOnHomepage } from 'integration/Functions/homepage';

it('Homepage promoted products - Adding product to cart from promoted products on homepage and check product in cart', () => {
    cy.visit('/');
    cy.wait(2000);
    addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    checkTotalPriceInCart(totalPrice.cart1);
    cy.url().should('contain', url.cart);
});
