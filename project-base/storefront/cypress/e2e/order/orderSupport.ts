import { DEFAULT_PERSIST_STORE_STATE, PERSIST_STORE_NAME, url, staticData } from 'fixtures/demodata';
import { generateCustomerRegistrationData, generateCreateOrderInput } from 'fixtures/generators';
import { changeElementText, checkAndHideSuccessToast, checkUrl, translations, t } from 'support';
import { TIDs } from 'tids';

export const fillEmailInThirdStep = (email: string) => {
    cy.get('#contact-information-form-email')
        .should('have.attr', 'placeholder', translations.placeholder.email)
        .type(email);
};

export const fillCustomerInformationInThirdStep = (phone: string, firstName: string, lastName: string) => {
    cy.get('#contact-information-form-telephone')
        .should('have.attr', 'placeholder', translations.placeholder.phone)
        .type(phone);
    cy.get('#contact-information-form-firstName')
        .should('have.attr', 'placeholder', translations.placeholder.firstName)
        .type(firstName);
    cy.get('#contact-information-form-lastName')
        .should('have.attr', 'placeholder', translations.placeholder.lastName)
        .type(lastName);
};

export const clearPostcodeInThirdStep = () => {
    cy.get('#contact-information-form-postcode').clear();
};

export const fillBillingAdressInThirdStep = (street: string, city: string, postCode: string) => {
    cy.get('#contact-information-form-street')
        .should('have.attr', 'placeholder', translations.placeholder.street)
        .type(street);
    cy.get('#contact-information-form-city')
        .should('have.attr', 'placeholder', translations.placeholder.city)
        .type(city);
    cy.get('#contact-information-form-postcode')
        .should('have.attr', 'placeholder', translations.placeholder.postCode)
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
        .should('have.attr', 'placeholder', translations.placeholder.firstName)
        .clear()
        .type(deliveryAddress.firstName);

    cy.get('#contact-information-form-deliveryLastName')
        .should('have.attr', 'placeholder', translations.placeholder.lastName)
        .clear()
        .type(deliveryAddress.lastName);

    cy.get('#contact-information-form-deliveryCompanyName')
        .should('have.attr', 'placeholder', translations.placeholder.company)
        .clear()
        .type(deliveryAddress.company);

    cy.get('#contact-information-form-deliveryTelephone')
        .should('have.attr', 'placeholder', translations.placeholder.phone)
        .clear()
        .type(deliveryAddress.phone);

    cy.get('#contact-information-form-deliveryStreet')
        .should('have.attr', 'placeholder', translations.placeholder.street)
        .clear()
        .type(deliveryAddress.street);

    cy.get('#contact-information-form-deliveryCity')
        .should('have.attr', 'placeholder', translations.placeholder.city)
        .clear()
        .type(deliveryAddress.city);

    cy.get('#contact-information-form-deliveryPostcode')
        .should('have.attr', 'placeholder', translations.placeholder.postCode)
        .clear({ force: true })
        .type(deliveryAddress.postCode, { force: true });

    cy.get('#contact-information-form-deliveryCountry').realClick();
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
        .should('have.attr', 'placeholder', translations.placeholder.firstName)
        .clear()
        .type(deliveryContact.firstName);

    cy.get('#contact-information-form-deliveryLastName')
        .should('have.attr', 'placeholder', translations.placeholder.lastName)
        .clear()
        .type(deliveryContact.lastName);

    cy.get('#contact-information-form-deliveryTelephone')
        .should('have.attr', 'placeholder', translations.placeholder.phone)
        .clear()
        .type(deliveryContact.phone);
};

export const fillRegistrationInfoAfterOrder = (password: string) => {
    cy.get('#registration-after-order-form-password')
        .should('have.attr', 'placeholder', translations.placeholder.password)
        .type(password);

    cy.get('#registration-after-order-form-passwordConfirm')
        .should('have.attr', 'placeholder', translations.placeholder.passwordAgain)
        .type(password);

    cy.get('[for="registration-after-order-form-privacyPolicy"]').find('span').first().click();
};

export const clickOnSendOrderButton = () => {
    cy.getByTID([TIDs.blocks_orderaction_next]).should('be.visible').and('not.be.disabled').click();
};

export const mouseOverUserMenuButton = () => {
    cy.getByTID([TIDs.my_account_link]).should('be.visible').realMouseMove(0, 10);
    cy.wait(1000);
    cy.getByTID([TIDs.my_account_link]).should('be.visible').realMouseMove(-1, -15);
    cy.wait(1000);
};

export const fillInNoteInThirdStep = (note: string) => {
    cy.get('#contact-information-form-note')
        .should('have.attr', 'placeholder', translations.placeholder.note)
        .type(note);
};

export const clickOnOrderDetailButtonOnThankYouPage = () => {
    // The link is rendered in HTML from backend with localized URL
    // Use the localized url path from fixtures (e.g., '/detail-objednavky' for Slovak)
    cy.getByTID([TIDs.pages_orderconfirmation]).find(`a[href*="${url.order.orderDetail}"]`).click();
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
    cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
    cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
    cy.createOrder({
        ...generateCreateOrderInput(email),
        isDeliveryAddressDifferentFromBilling: true,
        deliveryFirstName: staticData.deliveryAddress.firstName,
        deliveryLastName: staticData.deliveryAddress.lastName,
        deliveryCompanyName: staticData.deliveryAddress.company,
        deliveryTelephone: staticData.deliveryAddress.phone,
        deliveryStreet: staticData.deliveryAddress.street,
        deliveryCity: staticData.deliveryAddress.city,
        deliveryPostcode: staticData.deliveryAddress.postCode,
        deliveryCountry: staticData.deliveryAddress.country,
    });
    cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
    cy.preselectTransportForTest(secondTransportUuid ?? staticData.transport.czechPost.uuid, secondPickupPlaceUuid);
    cy.preselectPaymentForTest(secondPaymentUuid ?? staticData.payment.onDelivery.uuid);
};

export const fillBillingInfoForDeliveryAddressTests = () => {
    fillEmailInThirdStep(staticData.customer1.email);
    fillCustomerInformationInThirdStep(
        staticData.customer1.phone,
        staticData.customer1.firstName,
        staticData.customer1.lastName,
    );
    fillBillingAdressInThirdStep(
        staticData.customer1.billingStreet,
        staticData.customer1.billingCity,
        staticData.customer1.billingPostCode,
    );
};

export const checkThatContactInformationWasRemovedFromLocalStorage = () => {
    const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
    if (!currentAppStoreAsString) {
        throw new Error(
            'Could not load app store from local storage. This is an issue with tests, not with the application.',
        );
    }

    expect(currentAppStoreAsString).to.equal(JSON.stringify(DEFAULT_PERSIST_STORE_STATE));
};

export const checkTransportSelectionIsNotVisible = () => {
    cy.getByTID([TIDs.pages_order_transport]).should('not.exist');
};

export const checkEmptyCartTextIsVisible = () => {
    cy.getByTID([TIDs.cart_page_empty_cart_text]).should('exist').and('be.visible');
};

export const checkTransportSelectionIsVisible = () => {
    cy.getByTID([TIDs.pages_order_transport]).should('exist').and('be.visible');
};

export const checkContactInformationFormIsNotVisible = () => {
    cy.getByTID([TIDs.contact_information_form]).should('not.exist');
};

export const changeOrderDetailDynamicPartsToStaticDemodata = (shouldChangeBreadcrumb: boolean = false) => {
    changeElementText(TIDs.order_detail_number_heading, staticData.order.numberHeading);
    changeElementText(TIDs.order_detail_number, staticData.order.number);
    changeElementText(TIDs.order_detail_creation_date, staticData.order.creationDate, false);

    if (shouldChangeBreadcrumb) {
        changeElementText(TIDs.breadcrumbs_tail, staticData.order.number);
    }
};

export const changeOrdersListDynamicPartsToStaticDemodata = () => {
    changeElementText(TIDs.order_list_item_number, staticData.order.number, false);
    changeElementText(TIDs.order_list_item_date, staticData.order.creationDate, false);
};

export const changeOrderConfirmationDynamicPartsToStaticDemodata = () => {
    cy.getByTID([TIDs.order_confirmation_page_text_wrapper]).then((element) => {
        const originalText = element.html();
        element.html(originalText.replace(/\d{7,}/g, staticData.order.number));
    });
};

export const checkOrderConfirmationStatusText = (transportSpecificTextKey: string) => {
    cy.getByTID([TIDs.order_confirmation_page_text_wrapper])
        .should('exist')
        .should('contain.text', staticData.order.number);

    // Try both with and without HTML tags, as some keys have <b> tags in .po files
    const keyWithBold = `<b>${transportSpecificTextKey}</b>`;

    // Use dynamic translation - try the key with bold tags first, then without
    t(keyWithBold).then((translatedWithBold) => {
        // If translation with bold tags is found (not returned as key), use it
        if (translatedWithBold !== keyWithBold) {
            // Strip HTML tags for text comparison
            const textWithoutTags = translatedWithBold.replace(/<\/?b>/g, '');
            cy.getByTID([TIDs.order_confirmation_page_text_wrapper]).should('contain.text', textWithoutTags);
        } else {
            // Otherwise try without bold tags
            t(transportSpecificTextKey).then((translated) => {
                cy.getByTID([TIDs.order_confirmation_page_text_wrapper]).should('contain.text', translated);
            });
        }
    });
};

export const submitRegistrationFormAfterOrder = () => {
    cy.getByTID([TIDs.registration_after_order_submit_button]).click();
};

export const goToOrderDetailFromOrderList = (index: number = 0) => {
    cy.getByTID([[TIDs.my_orders_link_, index]]).click();
    checkUrl(url.order.orderDetail);
    cy.waitForStableAndInteractiveDOM();
};

export const checkOrderDetailFromOrderPage = (transportName: string, paymentName: string, note?: string) => {
    cy.getByTID([TIDs.order_detail_transport]).should('exist').and('be.visible').and('contain.text', transportName);
    cy.getByTID([TIDs.order_detail_payment]).should('exist').and('be.visible').and('contain.text', paymentName);

    if (note) {
        cy.getByTID([TIDs.order_detail_note])
            .should('exist')
            .and('be.visible')
            .and('contain.text', note ?? '');
    }

    cy.getByTID([TIDs.order_detail_items])
        .should('exist')
        .and('be.visible')
        .and('contain.text', staticData.products.helloKitty.name);

    cy.getByTID([TIDs.order_detail_repeat_order_button]).should('exist').and('be.visible');
};

export const checkOrderDetailFromOrderPageWithComplaintButton = (
    transportName: string,
    paymentName: string,
    note?: string,
) => {
    checkOrderDetailFromOrderPage(transportName, paymentName, note);
    cy.getByTID([TIDs.order_detail_create_complaint_button]).should('exist').and('be.visible');
};

export const checkOrderDetailFromOrderPageWithPromoCode = (
    transportName: string,
    paymentName: string,
    note?: string,
) => {
    checkOrderDetailFromOrderPage(transportName, paymentName, note);
    cy.getByTID([TIDs.order_detail_items])
        .should('exist')
        .and('be.visible')
        .and('contain.text', translations.order.promoCode);
};

export const repeatOrderFromOrderList = (withMerge?: boolean) => {
    cy.getByTID([TIDs.order_list_repeat_order_button]).click();

    if (withMerge === true) {
        cy.getByTID([TIDs.repeat_order_merge_carts_button]).click();
    } else if (withMerge === false) {
        cy.getByTID([TIDs.repeat_order_dont_merge_carts_button]).click();
    }
};

export const repeatOrderFromOrderDetail = (withMerge?: boolean) => {
    cy.getByTID([TIDs.order_detail_repeat_order_button]).click();

    if (withMerge === true) {
        cy.getByTID([TIDs.repeat_order_merge_carts_button]).click();
    } else if (withMerge === false) {
        cy.getByTID([TIDs.repeat_order_dont_merge_carts_button]).click();
    }
};

export const selectDeliveryAddressCard = (addressIndex: number = 0) => {
    cy.get(`[data-tid^="${TIDs.blocks_addresslist_addresscard_}"]`).eq(addressIndex).click();
};

export const clickAddNewAddressButton = () => {
    cy.getByTID([TIDs.blocks_addresslist_add_address_button]).click();
    cy.waitForStableAndInteractiveDOM();
};

export const fillAndSaveNewDeliveryAddressInPopup = (deliveryAddress: {
    firstName: string;
    lastName: string;
    company: string;
    phone: string;
    street: string;
    city: string;
    postCode: string;
}) => {
    cy.get('#delivery-address-form-firstName').clear().type(deliveryAddress.firstName);
    cy.get('#delivery-address-form-lastName').clear().type(deliveryAddress.lastName);
    cy.get('#delivery-address-form-companyName').clear().type(deliveryAddress.company);
    cy.get('#delivery-address-form-telephone').clear().type(deliveryAddress.phone);
    cy.get('#delivery-address-form-street').clear().type(deliveryAddress.street);
    cy.get('#delivery-address-form-city').clear().type(deliveryAddress.city);
    cy.get('#delivery-address-form-postcode').clear({ force: true }).type(deliveryAddress.postCode, { force: true });

    cy.get('#delivery-address-form-country').realClick();
    cy.realPress('{downarrow}');
    cy.realPress('{enter}');

    cy.getByTID([TIDs.delivery_address_form_submit_button]).click();
    cy.waitForStableAndInteractiveDOM();
    checkAndHideSuccessToast(translations.toast.success.deliveryAddressCreated);
};
