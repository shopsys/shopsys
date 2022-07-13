export function checkBasicInformationAndNoteInOrderDetail(note) {
    cy.get('[data-testid="pages-customer-orderdetail-creationDate"]');
    cy.get('[data-testid="pages-customer-orderdetail-note"]').contains(note);
}

export function checkBillingAdressInOrderDetail(firt_name, last_name, email, phone, street, city, postcode, country) {
    cy.get('[data-testid="pages-customer-orderdetail-firstName"]').contains(firt_name);
    cy.get('[data-testid="pages-customer-orderdetail-lastName"]').contains(last_name);
    cy.get('[data-testid="pages-customer-orderdetail-email"]').contains(email);
    cy.get('[data-testid="pages-customer-orderdetail-telephone"]').contains(phone);
    cy.get('[data-testid="pages-customer-orderdetail-street"]').contains(street);
    cy.get('[data-testid="pages-customer-orderdetail-city"]').contains(city);
    cy.get('[data-testid="pages-customer-orderdetail-postcode"]').contains(postcode);
    cy.get('[data-testid="pages-customer-orderdetail-country"]').contains(country);
}

export function checkDeliveryAdressInOrderDetail(firt_name, last_name, phone, street, city, postcode, country) {
    cy.get('[data-testid="pages-customer-orderdetail-deliveryFirstName"]').contains(firt_name);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryLastName"]').contains(last_name);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryTelephone"]').contains(phone);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryStreet"]').contains(street);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryCity"]').contains(city);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryPostcode"]').contains(postcode);
    cy.get('[data-testid="pages-customer-orderdetail-deliveryCountry"]').contains(country);
}

export function checkOneItemInOrderDetail(
    row_number,
    item_name,
    item_unit_price,
    item_quantity_with_unit,
    vat,
    item_price_without_vat,
    item_price_vat,
) {
    const item_name_selector =
        '[data-testid="pages-customer-orderdetail-item-' +
        row_number +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-name"]';
    const item_unite_price_selector =
        '[data-testid="pages-customer-orderdetail-item-' +
        row_number +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-unitprice"]';
    const item_quantity_selector =
        '[data-testid="pages-customer-orderdetail-item-' +
        row_number +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-quantity"]';
    const item_vat_selector =
        '[data-testid="pages-customer-orderdetail-item-' +
        row_number +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-vat"]';
    const item_price_without_vat_selector =
        '[data-testid="pages-customer-orderdetail-item-' +
        row_number +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-price"]';
    const item_price_vat_selector =
        '[data-testid="pages-customer-orderdetail-item-' +
        row_number +
        '"] ' +
        '[data-testid="pages-customer-orderdetail-item-pricevat"]';

    cy.get(item_name_selector).contains(item_name);
    cy.get(item_unite_price_selector).contains(item_unit_price);
    cy.get(item_quantity_selector).contains(item_quantity_with_unit);
    cy.get(item_vat_selector).contains(vat);
    cy.get(item_price_without_vat_selector).contains(item_price_without_vat);
    cy.get(item_price_vat_selector).contains(item_price_vat);
}
