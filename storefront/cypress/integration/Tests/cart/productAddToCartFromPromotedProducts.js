import { cart_total_price1, product1_catnum, product1_name_prefix_suffix, url_cart } from '../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/CartPopupWindow';
import { addProductToCartFromPromotedProductsOnHomepage } from '../../Functions/HomepagePage';

describe('Test for adding product to cart from promoted products', () => {
    it('Homepage promoted products - Adding product to cart from promoted products on homepage and check product in cart', () => {
        cy.visit('/');
        addProductToCartFromPromotedProductsOnHomepage(product1_catnum);
        checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
        checkProductInCart(product1_catnum, product1_name_prefix_suffix);
        checkTotalPriceInCart(cart_total_price1);
        cy.url().should('contain', url_cart);
    });
});
