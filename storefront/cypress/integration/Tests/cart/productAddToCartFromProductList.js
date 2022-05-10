import {
    cart_total_price1,
    category1_name,
    category1_url,
    product1_catnum,
    product1_name_prefix_suffix,
    url_cart,
} from '../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/CartPopupWindow';
import { clickOnCategoryFromMenu } from '../../Functions/HeaderPage';
import { addProductToCartFromProductList } from '../../Functions/ProductListPage';

describe('Test for adding product to cart from product list', () => {
    it('Product list - Adding product to cart from product list and check product in cart', () => {
        cy.visit('/');
        clickOnCategoryFromMenu(category1_name);
        cy.url().should('contain', category1_url);
        addProductToCartFromProductList(product1_catnum);
        checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
        checkProductInCart(product1_catnum, product1_name_prefix_suffix);
        checkTotalPriceInCart(cart_total_price1);
        cy.url().should('contain', url_cart);
    });
});
