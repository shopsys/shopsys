import { textCode, url } from 'fixtures/demodata';

export const checkProductInCart = (productCatnum: string, productName: string) => {
    const cartProductItemSelector =
        '[data-testid="pages-cart-list-item-' +
        productCatnum +
        '"] ' +
        '[data-testid="pages-cart-list-item-iteminfo-name"]';
    const productCatnumWebString = textCode + ': ' + productCatnum;
    cy.get(cartProductItemSelector).contains(productName);
    cy.get(cartProductItemSelector).contains(productCatnumWebString);
    cy.url().should('contain', url.cart);
};

export const checkTotalPriceInCart = (totalPrice: string) => {
    cy.get('[data-testid="pages-cart-cartpreview-total"]').contains(totalPrice);
};
