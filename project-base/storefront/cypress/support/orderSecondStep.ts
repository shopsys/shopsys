import { quantityUnit, transport } from 'fixtures/demodata';

export const continueToSecondStep = () => {
    cy.getByDataTestId('blocks-orderaction-next').click();
};

export const checkTransportPrice = (position: number, transportPrice: string) => {
    cy.getByDataTestId(['pages-order-transport', 'pages-order-transport-item']).eq(position).contains(transportPrice);
};

export const chooseTransportPersonalCollectionAndStore = (storeName: string) => {
    cy.getByDataTestId('pages-order-selectitem-label-name').contains(transport.personalCollection.name).click();
    cy.getByDataTestId('layout-popup');
    cy.getByDataTestId('pages-order-selectitem-label-name').contains(storeName).click();
    cy.getByDataTestId('pages-order-pickupplace-popup-confirm').click();
};

export const chooseTransportToHome = (transportName: string) => {
    cy.getByDataTestId(['pages-order-transport', 'pages-order-selectitem-label-name'])
        .contains(transportName)
        .click('left');
};

export const checkSelectedStoreInTransportList = (storeName: string) => {
    cy.getByDataTestId('pages-order-selectitem-label-place').contains(storeName);
};

export const choosePayment = (paymentName: string) => {
    cy.getByDataTestId(['pages-order-payment', 'pages-order-selectitem-label-name'])
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
    cy.getByDataTestId('blocks-ordersummary-singleproduct-count').contains(productQuantityWithUnit);
    cy.getByDataTestId('blocks-ordersummary-singleproduct-name').contains(productName);
    cy.getByDataTestId('blocks-ordersummary-singleproduct-price').contains(productPrice);
    cy.getByDataTestId('blocks-ordersummary-transport-name').contains(transportName);
    cy.getByDataTestId('blocks-ordersummary-transport-price').contains(transportPrice);
    cy.getByDataTestId('blocks-ordersummary-payment-name').contains(paymentName);
    cy.getByDataTestId('blocks-ordersummary-payment-price').contains(paymentPrice);
    cy.getByDataTestId('blocks-ordersummary-totalprice-amount').contains(totalOrderPrice);
};

export const continueToThirdStep = () => {
    cy.getByDataTestId('blocks-orderaction-next').click();
};
