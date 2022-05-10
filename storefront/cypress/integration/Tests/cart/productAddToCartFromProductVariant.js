import {
    cart_total_price2,
    product2_catnum,
    product2_name,
    product2_url,
    product3_catnum,
    product3_name,
    url_cart,
} from '../../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../../Functions/CartPopupWindow';
import { productClickFromPromotedProductsOnHomepage } from '../../../Functions/HomepagePage';
import { addProductVariantToCartFromProductDetail } from '../../../Functions/ProductDetailPage';

describe('Test for adding product to cart from product variant', () => {
    it('Product variant - Adding variant product to cart from product detail and check product in cart', () => {
        cy.visit('/');
        productClickFromPromotedProductsOnHomepage(product2_catnum, product2_name);
        cy.url().should('contain', product2_url);
        addProductVariantToCartFromProductDetail(product3_catnum);
        checkProductAndGoToCartFromCartPopupWindow(product3_name);
        checkProductInCart(product3_catnum, product3_name);
        checkTotalPriceInCart(cart_total_price2);
        cy.url().should('contain', url_cart);
    });
});
