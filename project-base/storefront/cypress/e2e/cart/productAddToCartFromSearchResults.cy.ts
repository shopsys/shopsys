import { products, totalPrice, url } from 'fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from 'support/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from 'support/cartPopupWindow';
import { searchProductByNameTypeEnterAndCheckResult } from 'support/header';
import { addProductToCartFromProductList } from 'support/productList';

it('Search results - Adding product to cart from search results list and check product in cart', () => {
    cy.visit('/');
    searchProductByNameTypeEnterAndCheckResult(products.helloKitty.name, products.helloKitty.catnum);
    cy.wait(5000);
    addProductToCartFromProductList(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    checkTotalPriceInCart(totalPrice.cart1);
    cy.url().should('contain', url.cart);
});