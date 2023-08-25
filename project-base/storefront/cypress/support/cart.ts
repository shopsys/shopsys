import { textCode, url } from 'fixtures/demodata';

export const checkProductInCart = (productCatnum: string, productName: string) => {
    const getCartItemName = () =>
        cy.getByDataTestId(['pages-cart-list-item-' + productCatnum, 'pages-cart-list-item-iteminfo-name']);
    getCartItemName().contains(productName);
    getCartItemName().contains(textCode + ': ' + productCatnum);
    cy.url().should('contain', url.cart);
};

export const checkTotalPriceInCart = (totalPrice: string) => {
    cy.getByDataTestId('pages-cart-cartpreview-total').contains(totalPrice);
};
