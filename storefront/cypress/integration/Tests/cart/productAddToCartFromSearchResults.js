import {
    cart_total_price1,
    product1_catnum,
    product1_name,
    product1_name_prefix_suffix,
    url_cart,
} from '../../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../../Functions/CartPopupWindow';
import { searchProductByNameTypeEnterAndCheckResult } from '../../../Functions/HeaderPage';
import { addProductToCartFromProductList } from '../../../Functions/ProductListPage';

describe('Test for adding product to cart from search results', () => {
    it('Search results - Adding product to cart from search results list and check product in cart', () => {
        cy.visit('/');
        searchProductByNameTypeEnterAndCheckResult(product1_name, product1_catnum);
        addProductToCartFromProductList(product1_catnum);
        checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
        checkProductInCart(product1_catnum, product1_name_prefix_suffix);
        checkTotalPriceInCart(cart_total_price1);
        cy.url().should('contain', url_cart);
    });
});
