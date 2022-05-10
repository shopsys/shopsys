import {
    brand_name1,
    cart_total_price1,
    product1_catnum,
    product1_name_prefix_suffix,
    url_brand_overview,
    url_cart,
} from '../../../fixtures/demodata';
import { checkProductInCart, checkTotalPriceInCart } from '../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/CartPopupWindow';
import { addProductToCartFromProductList } from '../../Functions/ProductListPage';

describe('Test for adding product to cart from brand list', () => {
    beforeEach(() => {
        cy.intercept('POST', '/graphql/').as('preview');
    });

    it('Brand list - Adding product to cart from brand list and check product in cart', () => {
        cy.visit(url_brand_overview);
        cy.wait('@preview');
        cy.get('[data-testid="blocks-simplenavigation-22"]').contains(brand_name1).click();
        addProductToCartFromProductList(product1_catnum);
        checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
        checkProductInCart(product1_catnum, product1_name_prefix_suffix);
        checkTotalPriceInCart(cart_total_price1);
        cy.url().should('contain', url_cart);
    });
});
