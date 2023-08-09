import { categories, products, totalPrice, url } from '../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../Functions/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/cartPopupWindow';
import { clickOnCategoryFromMenu } from '../../Functions/header';
import { addProductToCartFromProductList } from '../../Functions/productList';

it('Product list - Adding product to cart from product list and check product in cart', () => {
    cy.visit('/');
    clickOnCategoryFromMenu(categories.elektro.name);
    cy.url().should('contain', categories.elektro.url);
    cy.wait(6000);
    addProductToCartFromProductList(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    checkTotalPriceInCart(totalPrice.cart1);
    cy.url().should('contain', url.cart);
});
