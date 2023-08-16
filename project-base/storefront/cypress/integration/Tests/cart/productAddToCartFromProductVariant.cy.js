import { products, totalPrice, url } from '../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../Functions/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/cartPopupWindow';
import { addProductVariantToCartFromProductDetail } from '../../Functions/productDetail';

it('Product variant - Adding variant product to cart from product detail and check product in cart', () => {
    cy.visit(products.philips32PFL4308.url);
    cy.url().should('contain', products.philips32PFL4308.url);
    addProductVariantToCartFromProductDetail(products.philips54CRT.catnum);
    cy.wait(3000);
    checkProductAndGoToCartFromCartPopupWindow(products.philips54CRT.name);
    checkProductInCart(products.philips54CRT.catnum, products.philips54CRT.name);
    checkTotalPriceInCart(totalPrice.cart2);
    cy.url().should('contain', url.cart);
});
