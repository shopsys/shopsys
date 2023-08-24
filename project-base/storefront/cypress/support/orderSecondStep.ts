import { quantityUnit, transport } from 'fixtures/demodata';

export const continueToSecondStep = () => {
    cy.get('[data-testid="blocks-orderaction-next"]').click();
};

export const checkTransportPrice = (position: number, transportPrice: string) => {
    cy.get('[data-testid="pages-order-transport"] [data-testid="pages-order-transport-item"]')
        .eq(position)
        .contains(transportPrice);
};

export const chooseTransportPersonalCollectionAndStore = (storeName: string) => {
    cy.get('[data-testid="pages-order-selectitem-label-name"]').contains(transport.personalCollection.name).click();
    cy.get('[data-testid="layout-popup"]');
    cy.get('[data-testid="pages-order-selectitem-label-name"]').contains(storeName).click();
    cy.get('[data-testid="pages-order-pickupplace-popup-confirm"]').click();
};

export const chooseTransportToHome = (transportName: string) => {
    cy.get('[data-testid="pages-order-transport"] [data-testid="pages-order-selectitem-label-name"]')
        .contains(transportName)
        .click('left');
};

export const checkSelectedStoreInTransportList = (storeName: string) => {
    cy.get('[data-testid="pages-order-selectitem-label-place"]').contains(storeName);
};

export const choosePayment = (paymentName: string) => {
    cy.get('[data-testid="pages-order-payment"] [data-testid="pages-order-selectitem-label-name"]')
        .contains(paymentName)
        .click('left');
};

export const checkOrderSummaryWithOneItem = (
    productName: string,
    productQuantity: number,
    productPrice: string,
    transportName: string,
    transportPrice: string,
    paymentName: string,
    paymentPrice: string,
    totalOrderPrice: string,
) => {
    const productQuantityWithUnit = productQuantity + ' ' + quantityUnit;
    cy.get('[data-testid="blocks-ordersummary-singleproduct-count"]').contains(productQuantityWithUnit);
    cy.get('[data-testid="blocks-ordersummary-singleproduct-name"]').contains(productName);
    cy.get('[data-testid="blocks-ordersummary-singleproduct-price"]').contains(productPrice);
    cy.get('[data-testid="blocks-ordersummary-transport-name"]').contains(transportName);
    cy.get('[data-testid="blocks-ordersummary-transport-price"]').contains(transportPrice);
    cy.get('[data-testid="blocks-ordersummary-payment-name"]').contains(paymentName);
    cy.get('[data-testid="blocks-ordersummary-payment-price"]').contains(paymentPrice);
    cy.get('[data-testid="blocks-ordersummary-totalprice-amount"]').contains(totalOrderPrice);
};

export const continueToThirdStep = () => {
    cy.get('[data-testid="blocks-orderaction-next"]').click();
};
