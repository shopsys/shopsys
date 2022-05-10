import {
    cart_total_price1,
    product1_catnum,
    product1_name,
    product1_name_prefix_suffix,
    product1_url_prefix_suffix,
    url_cart,
} from '../../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../../Functions/CartPopupWindow';
import { productClickFromPromotedProductsOnHomepage } from '../../../Functions/HomepagePage';
import { addProductToCartFromProductDetail } from '../../../Functions/ProductDetailPage';

describe('Test for adding product to cart from product detail', () => {
    it('Product detail - Adding product to cart from product detail and check product in cart', () => {
        cy.visit('/');
        productClickFromPromotedProductsOnHomepage(product1_catnum, product1_name);
        cy.url().should('contain', product1_url_prefix_suffix);
        addProductToCartFromProductDetail();
        checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
        checkProductInCart(product1_catnum, product1_name_prefix_suffix);
        checkTotalPriceInCart(cart_total_price1);
        cy.url().should('contain', url_cart);
    });
});
