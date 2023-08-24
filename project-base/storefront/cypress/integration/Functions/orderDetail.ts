export function checkBasicInformationAndNoteInOrderDetail(note: string) {
    cy.get('[data-testid="pages-customer-orderdetail-creationDate"]');
    cy.get('[data-testid="pages-customer-orderdetail-note"]').contains(note);
}

export function checkBillingAdressInOrderDetail(
    firtName: string,
    lastName: string,
    email: string,
    phone: string,
    street: string,
    city: string,
    postCode: string,
    country: string,
) {
    cy.get('[data-testid="pages-customer-orderdetail-firstName"]').contains(firtName);
    cy.get('[data-testid="pages-customer-orderdetail-lastName"]').contains(lastName);
    cy.get('[data-testid="pages-customer-orderdetail-email"]').contains(email);
    cy.get('[data-testid="pages-customer-orderdetail-telephone"]').contains(phone);
    cy.get('[data-testid="pages-customer-orderdetail-street"]').contains(street);
    cy.get('[data-testid="pages-customer-orderdetail-city"]').contains(city);
    cy.get('[data-testid="pages-customer-orderdetail-postcode"]').contains(postCode);
    cy.get('[data-testid="pages-customer-orderdetail-country"]').contains(country);
}

export function checkDeliveryAdressInOrderDetail(
    firtName: string,
    lastName: string,
    phone: string,
    street: string,
    city: string,
    postCode: string,
    country: string,
) {
    cy.get('[data-testid="pages-customer-orderdetail-deliveryFirstName"]').contains(firtName);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryLastName"]').contains(lastName);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryTelephone"]').contains(phone);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryStreet"]').contains(street);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryCity"]').contains(city);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryPostcode"]').contains(postCode);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryCountry"]').contains(country);
}

export function checkOneItemInOrderDetail(
    rowNumber: number,
    itemName: string,
    itemUnitPrice: string,
    itemQuantity: number,
    vat: string,
    itemPriceWithoutVat: string,
    itemPriceVat: string,
) {
    const itemNameSelector =
        '[data-testid="pages-customer-orderdetail-item-' +
        rowNumber +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-name"]';
    const itemUnitPriceSelector =
        '[data-testid="pages-customer-orderdetail-item-' +
        rowNumber +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-unitprice"]';
    const itemQuantitySelector =
        '[data-testid="pages-customer-orderdetail-item-' +
        rowNumber +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-quantity"]';
    const itemVatSelector =
        '[data-testid="pages-customer-orderdetail-item-' +
        rowNumber +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-vat"]';
    const itemPriceWithoutVatSelector =
        '[data-testid="pages-customer-orderdetail-item-' +
        rowNumber +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-price"]';
    const itemPriceVatSelector =
        '[data-testid="pages-customer-orderdetail-item-' +
        rowNumber +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-pricevat"]';

    cy.get(itemNameSelector).contains(itemName);
    cy.get(itemUnitPriceSelector).contains(itemUnitPrice);
    cy.get(itemQuantitySelector).contains(itemQuantity);
    cy.get(itemVatSelector).contains(vat);
    cy.get(itemPriceWithoutVatSelector).contains(itemPriceWithoutVat);
    cy.get(itemPriceVatSelector).contains(itemPriceVat);
}
