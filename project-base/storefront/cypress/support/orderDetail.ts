export const checkBasicInformationAndNoteInOrderDetail = (note: string) => {
    cy.getByDataTestId('pages-customer-orderdetail-creationDate');
    cy.getByDataTestId('pages-customer-orderdetail-note').contains(note);
};

export const checkBillingAdressInOrderDetail = (
    firtName: string,
    lastName: string,
    email: string,
    phone: string,
    street: string,
    city: string,
    postCode: string,
    country: string,
) => {
    cy.getByDataTestId('pages-customer-orderdetail-firstName').contains(firtName);
    cy.getByDataTestId('pages-customer-orderdetail-lastName').contains(lastName);
    cy.getByDataTestId('pages-customer-orderdetail-email').contains(email);
    cy.getByDataTestId('pages-customer-orderdetail-telephone').contains(phone);
    cy.getByDataTestId('pages-customer-orderdetail-street').contains(street);
    cy.getByDataTestId('pages-customer-orderdetail-city').contains(city);
    cy.getByDataTestId('pages-customer-orderdetail-postcode').contains(postCode);
    cy.getByDataTestId('pages-customer-orderdetail-country').contains(country);
};

export const checkDeliveryAdressInOrderDetail = (
    firtName: string,
    lastName: string,
    phone: string,
    street: string,
    city: string,
    postCode: string,
    country: string,
) => {
    cy.getByDataTestId('pages-customer-orderdetail-deliveryFirstName').contains(firtName);
    cy.getByDataTestId('pages-customer-orderdetail-deliveryLastName').contains(lastName);
    cy.getByDataTestId('pages-customer-orderdetail-deliveryTelephone').contains(phone);
    cy.getByDataTestId('pages-customer-orderdetail-deliveryStreet').contains(street);
    cy.getByDataTestId('pages-customer-orderdetail-deliveryCity').contains(city);
    cy.getByDataTestId('pages-customer-orderdetail-deliveryPostcode').contains(postCode);
    cy.getByDataTestId('pages-customer-orderdetail-deliveryCountry').contains(country);
};

export const checkOneItemInOrderDetail = (
    rowNumber: number,
    itemName: string,
    itemUnitPrice: string,
    itemQuantity: number,
    vat: string,
    itemPriceWithoutVat: string,
    itemPriceVat: string,
) => {
    const getOrderDetailRow = () => cy.getByDataTestId('pages-customer-orderdetail-item-' + rowNumber);
    getOrderDetailRow().getByDataTestId('pages-customer-orderdetail-item-name').contains(itemName);
    getOrderDetailRow().getByDataTestId('pages-customer-orderdetail-item-unitprice').contains(itemUnitPrice);
    getOrderDetailRow().getByDataTestId('pages-customer-orderdetail-item-quantity').contains(itemQuantity);
    getOrderDetailRow().getByDataTestId('pages-customer-orderdetail-item-vat').contains(vat);
    getOrderDetailRow().getByDataTestId('pages-customer-orderdetail-item-price').contains(itemPriceWithoutVat);
    getOrderDetailRow().getByDataTestId('pages-customer-orderdetail-item-pricevat').contains(itemPriceVat);
};
