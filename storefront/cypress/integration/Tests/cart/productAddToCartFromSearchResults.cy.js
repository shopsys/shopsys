import { products, totalPrice, url } from '../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../Functions/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/cartPopupWindow';
import { searchProductByNameTypeEnterAndCheckResult } from '../../Functions/header';
import { addProductToCartFromProductList } from '../../Functions/productList';

it('Search results - Adding product to cart from search results list and check product in cart', () => {
    cy.visit('/');
    searchProductByNameTypeEnterAndCheckResult(products.helloKitty.name, products.helloKitty.catnum);
    addProductToCartFromProductList(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    checkTotalPriceInCart(totalPrice.cart1);
    cy.url().should('contain', url.cart);
});
