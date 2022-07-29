import { quantityUnit, transport } from '../../fixtures/demodata';

export function continueToSecondStep() {
    cy.get('[data-testid="blocks-orderaction-next"]').click();
}

export function checkTransportPrice(position, transportPrice) {
    cy.get('[data-testid="pages-order-transport"] [data-testid="pages-order-transport-item"]')
        .eq(position)
        .contains(transportPrice);
}

export function chooseTransportPersonalCollectionAndStore(storeName) {
    cy.get('[data-testid="pages-order-selectitem-label-name"]').contains(transport.personalCollection.name).click();
    cy.get('[data-testid="layout-popup"]');
    cy.get('[data-testid="pages-order-selectitem-label-name"]').contains(storeName).click();
    cy.get('[data-testid="pages-order-pickupplace-popup-confirm"]').click();
}

export function chooseTransportToHome(transportName) {
    cy.get('[data-testid="pages-order-transport"] [data-testid="pages-order-selectitem-label-name"]')
        .contains(transportName)
        .click('left');
}

export function checkSelectedStoreInTransportList(storeName) {
    cy.get('[data-testid="pages-order-selectitem-label-place"]').contains(storeName);
}

export function choosePayment(paymentName) {
    cy.get('[data-testid="pages-order-payment"] [data-testid="pages-order-selectitem-label-name"]')
        .contains(paymentName)
        .click('left');
}

export function checkOrderSummaryWithOneItem(
    productName,
    productQuantity,
    productPrice,
    transportName,
    transportPrice,
    paymentName,
    paymentPrice,
    totalOrderPrice,
) {
    const productQuantityWithUnit = productQuantity + ' ' + quantityUnit;
    cy.get('[data-testid="blocks-ordersummary-singleproduct-count"]').contains(productQuantityWithUnit);
    cy.get('[data-testid="blocks-ordersummary-singleproduct-name"]').contains(productName);
    cy.get('[data-testid="blocks-ordersummary-singleproduct-price"]').contains(productPrice);
    cy.get('[data-testid="blocks-ordersummary-transport-name"]').contains(transportName);
    cy.get('[data-testid="blocks-ordersummary-transport-price"]').contains(transportPrice);
    cy.get('[data-testid="blocks-ordersummary-payment-name"]').contains(paymentName);
    cy.get('[data-testid="blocks-ordersummary-payment-price"]').contains(paymentPrice);
    cy.get('[data-testid="blocks-ordersummary-totalprice-amount"]').contains(totalOrderPrice);
}

export function continueToThirdStep() {
    cy.get('[data-testid="blocks-orderaction-next"]').click();
}
