import { brandSencor, products, totalPrice, url } from '../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../Functions/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/cartPopupWindow';
import { addProductToCartFromProductList } from '../../Functions/productList';

it('Brand list - Adding product to cart from brand list and check product in cart', () => {
    cy.visit(url.brandOverwiev);
    cy.get('[data-testid="blocks-simplenavigation-22"]').contains(brandSencor).click();
    addProductToCartFromProductList(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    checkTotalPriceInCart(totalPrice.cart1);
    cy.url().should('contain', url.cart);
});
