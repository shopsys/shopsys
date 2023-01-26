import { products, totalPrice, url } from '../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../Functions/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/cartPopupWindow';
import { productClickFromPromotedProductsOnHomepage } from '../../Functions/homepage';
import { addProductToCartFromProductDetail } from '../../Functions/productDetail';

it('Product detail - Adding product to cart from product detail and check product in cart', () => {
    cy.visit('/');
    productClickFromPromotedProductsOnHomepage(products.helloKitty.catnum, products.helloKitty.name);
    cy.url().should('contain', products.helloKitty.urlPrefixSuffix);
    addProductToCartFromProductDetail();
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    checkTotalPriceInCart(totalPrice.cart1);
    cy.url().should('contain', url.cart);
});
