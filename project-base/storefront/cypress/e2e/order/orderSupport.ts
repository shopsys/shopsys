import {
    DEFAULT_APP_STORE,
    customer1,
    deliveryAddress,
    link,
    payment,
    placeholder,
    transport,
    url,
} from 'fixtures/demodata';
import { generateCustomerRegistrationData, generateCreateOrderInput } from 'fixtures/generators';
import { TIDs } from 'tids';

export const fillEmailInThirdStep = (email: string) => {
    cy.get('#contact-information-form-email').should('have.attr', 'placeholder', placeholder.email).type(email);
};

export const clearEmailInThirdStep = () => {
    cy.get('#contact-information-form-email').clear();
};

export const fillCustomerInformationInThirdStep = (phone: string, firstName: string, lastName: string) => {
    cy.get('#contact-information-form-telephone').should('have.attr', 'placeholder', placeholder.phone).type(phone);
    cy.get('#contact-information-form-firstName')
        .should('have.attr', 'placeholder', placeholder.firstName)
        .type(firstName);
    cy.get('#contact-information-form-lastName')
        .should('have.attr', 'placeholder', placeholder.lastName)
        .type(lastName);
};

export const clearPostcodeInThirdStep = () => {
    cy.get('#contact-information-form-postcode').clear();
};

export const fillBillingAdressInThirdStep = (street: string, city: string, postCode: string) => {
    cy.get('#contact-information-form-street').should('have.attr', 'placeholder', placeholder.street).type(street);
    cy.get('#contact-information-form-city').should('have.attr', 'placeholder', placeholder.city).type(city);
    cy.get('#contact-information-form-postcode')
        .should('have.attr', 'placeholder', placeholder.postCode)
        .type(postCode, { force: true });
};

export const clearAndFillDeliveryAdressInThirdStep = (deliveryAddress: {
    firstName: string;
    lastName: string;
    company: string;
    phone: string;
    street: string;
    city: string;
    postCode: string;
}) => {
    cy.get('#contact-information-form-deliveryFirstName')
        .should('have.attr', 'placeholder', placeholder.firstName)
        .clear()
        .type(deliveryAddress.firstName);
    cy.get('#contact-information-form-deliveryLastName')
        .should('have.attr', 'placeholder', placeholder.lastName)
        .clear()
        .type(deliveryAddress.lastName);
    cy.get('#contact-information-form-deliveryCompanyName')
        .should('have.attr', 'placeholder', placeholder.company)
        .clear()
        .type(deliveryAddress.company);
    cy.get('#contact-information-form-deliveryTelephone')
        .should('have.attr', 'placeholder', placeholder.phone)
        .clear()
        .type(deliveryAddress.phone);
    cy.get('#contact-information-form-deliveryStreet')
        .should('have.attr', 'placeholder', placeholder.street)
        .clear()
        .type(deliveryAddress.street);
    cy.get('#contact-information-form-deliveryCity')
        .should('have.attr', 'placeholder', placeholder.city)
        .clear()
        .type(deliveryAddress.city);
    cy.get('#contact-information-form-deliveryPostcode')
        .should('have.attr', 'placeholder', placeholder.postCode)
        .clear({ force: true })
        .type(deliveryAddress.postCode, { force: true });

    cy.get('#deliveryCountry-select').realClick();
    // we cannot clear the select value, so we press downarrow
    // each time, which always changes the current selection
    cy.realPress('{downarrow}');
    cy.realPress('{enter}');
};

export const clearAndFillDeliveryContactInThirdStep = (deliveryContact: {
    firstName: string;
    lastName: string;
    phone: string;
}) => {
    cy.get('#contact-information-form-deliveryFirstName')
        .should('have.attr', 'placeholder', placeholder.firstName)
        .clear()
        .type(deliveryContact.firstName);
    cy.get('#contact-information-form-deliveryLastName')
        .should('have.attr', 'placeholder', placeholder.lastName)
        .clear()
        .type(deliveryContact.lastName);
    cy.get('#contact-information-form-deliveryTelephone')
        .should('have.attr', 'placeholder', placeholder.phone)
        .clear()
        .type(deliveryContact.phone);
};

export const fillRegistrationInfoAfterOrder = (password: string) => {
    cy.get('#registration-after-order-form-password')
        .should('have.attr', 'placeholder', placeholder.password)
        .type(password);
    cy.get('[for="registration-after-order-form-privacyPolicy"]').find('div').first().click();
};

export const clickOnSendOrderButton = () => {
    cy.getByTID([TIDs.blocks_orderaction_next]).click();
};

export const fillInNoteInThirdStep = (note: string) => {
    cy.get('#contact-information-form-note').should('have.attr', 'placeholder', placeholder.note).type(note);
};

export const checkFinishOrderPageAsUnregistredCustomer = () => {
    cy.url().should('contain', url.order.orderConfirmation);
    cy.getByTID([TIDs.pages_orderconfirmation])
        .get('#registration-after-order-form-password')
        .should('have.attr', 'placeholder', placeholder.password);
};

export const checkFinishOrderPageAsUnloggedCustomerWithEmailWithExistingRegistration = () => {
    cy.url().should('contain', url.order.orderConfirmation);
    cy.getByTID([TIDs.pages_orderconfirmation]).get('#registration-after-order-form-password').should('not.exist');
};

export const clickOnOrderDetailButtonOnThankYouPage = () => {
    cy.getByTID([TIDs.pages_orderconfirmation]).contains(link.orderDetail).click();
    cy.url().should('contain', url.order.orderDetail);
};

export const registerAndCreateOrderForDeliveryAddressTests = (
    email: string,
    secondTransportUuid?: string,
    secondPickupPlaceUuid?: string,
    secondPaymentUuid?: string,
) => {
    cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
    cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
    cy.preselectTransportForTest(transport.czechPost.uuid);
    cy.preselectPaymentForTest(payment.onDelivery.uuid);
    cy.createOrder({
        ...generateCreateOrderInput(email),
        differentDeliveryAddress: true,
        deliveryFirstName: deliveryAddress.firstName,
        deliveryLastName: deliveryAddress.lastName,
        deliveryCompanyName: deliveryAddress.company,
        deliveryTelephone: deliveryAddress.phone,
        deliveryStreet: deliveryAddress.street,
        deliveryCity: deliveryAddress.city,
        deliveryPostcode: deliveryAddress.postCode,
        deliveryCountry: deliveryAddress.country,
    });
    cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
    cy.preselectTransportForTest(secondTransportUuid ?? transport.czechPost.uuid, secondPickupPlaceUuid);
    cy.preselectPaymentForTest(secondPaymentUuid ?? payment.onDelivery.uuid);
};

export const fillBillingInfoForDeliveryAddressTests = () => {
    fillEmailInThirdStep(customer1.email);
    fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
    fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
};

export const checkThatContactInformationWasRemovedFromLocalStorage = () => {
    const currentAppStoreAsString = window.localStorage.getItem('app-store');
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    expect(currentAppStoreAsString).to.equal(JSON.stringify(DEFAULT_APP_STORE));
};

export const goToMyOrdersFromHeader = () => {
    cy.getByTID([TIDs.my_account_link])
        .should('be.visible')
        .realHover()
        .then(() => cy.getByTID([TIDs.header_my_orders_link]).should('be.visible').click());
};
