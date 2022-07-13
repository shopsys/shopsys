import { currency, quantityUnit, transport } from '../../fixtures/demodata';

export function continueToSecondStep() {
    cy.get('[data-testid="blocks-orderaction-next"]').click();
}

export function checkTransportPrice(position, transport_price) {
    cy.get('[data-testid="pages-order-transport"] [data-testid="pages-order-transport-item"]')
        .eq(position)
        .contains(transport_price);
}

export function chooseTransportPersonalCollectionAndStore(storeName) {
    cy.get('[data-testid="pages-order-selectitem-label-name"]').contains(transport.personalCollection.name).click();
    cy.get('[data-testid="layout-popup"]');
    cy.get('[data-testid="pages-order-selectitem-label-name"]').contains(store_name).click();
    cy.get('[data-testid="pages-order-pickupplace-popup-confirm"]').click();
}

export function chooseTransportToHome(transport_name) {
    cy.get('[data-testid="pages-order-transport"] [data-testid="pages-order-selectitem-label-name"]')
        .contains(transport_name)
        .click('left');
}

export function checkSelectedStoreInTransportList(store_name) {
    cy.get('[data-testid="pages-order-selectitem-label-place"]').contains(store_name);
}

export function choosePayment(payment_name) {
    cy.get('[data-testid="pages-order-payment"] [data-testid="pages-order-selectitem-label-name"]')
        .contains(payment_name)
        .click('left');
}

export function checkOrderSummaryWithOneItem(
    product_name,
    product_quantity,
    product_price,
    transport_name,
    transport_price,
    payment_name,
    payment_price,
    total_order_price,
) {
    const product_quantity_with_unit = product_quantity + ' ' + quantity_unit1;
    const product_price_with_currency = product_price + ' ' + currency;
    cy.get('[data-testid="blocks-ordersummary-singleproduct-count"]').contains(product_quantity_with_unit);
    cy.get('[data-testid="blocks-ordersummary-singleproduct-name"]').contains(product_name);
    cy.get('[data-testid="blocks-ordersummary-singleproduct-price"]').contains(product_price_with_currency);
    cy.get('[data-testid="blocks-ordersummary-transport-name"]').contains(transport_name);
    cy.get('[data-testid="blocks-ordersummary-transport-price"]').contains(transport_price);
    cy.get('[data-testid="blocks-ordersummary-payment-name"]').contains(payment_name);
    cy.get('[data-testid="blocks-ordersummary-payment-price"]').contains(payment_price);
    cy.get('[data-testid="blocks-ordersummary-totalprice-amount"]').contains(total_order_price);
}

export function continueToThirdStep() {
    cy.get('[data-testid="blocks-orderaction-next"]').click();
}
