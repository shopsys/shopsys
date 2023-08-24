import { products, totalPrice, url } from 'fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from 'support/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from 'support/cartPopupWindow';
import { productClickFromPromotedProductsOnHomepage } from 'support/homepage';
import { addProductToCartFromProductDetail } from 'support/productDetail';

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
