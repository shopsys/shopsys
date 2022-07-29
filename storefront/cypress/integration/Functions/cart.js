import { textCode, url } from '../../fixtures/demodata';

export function checkProductInCart(catnum, productName) {
    const cartProductItemSelector =
        '[data-testid="pages-cart-list-item-' + catnum + '"] ' + '[data-testid="pages-cart-list-item-iteminfo-name"]';
    const productCatnum = textCode + ': ' + catnum;
    cy.get(cartProductItemSelector).contains(productName);
    cy.get(cartProductItemSelector).contains(productCatnum);
    cy.url().should('contain', url.cart);
}

export function checkTotalPriceInCart(totalPrice) {
    cy.get('[data-testid="pages-cart-cartpreview-total"]').contains(totalPrice);
}
