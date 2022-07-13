/// <reference types="Cypress" />
import {
    cart_total_price1,
    countryCZ,
    customer1_billing_city,
    customer1_billing_street,
    customer1_billing_zip,
    customer1_email,
    customer1_first_name,
    customer1_last_name,
    customer1_phone,
    free_price,
    order_note1,
    payment1_name,
    product1_catnum,
    product1_name_prefix_suffix,
    product1_price,
    product1_price_without_vat,
    standart_rate,
    store1_city,
    store1_name,
    store1_postcode,
    store1_street,
    transport3_name,
    url_cart,
    url_order_detail,
    url_order_second_step,
    url_order_third_step,
    zero_rate,
} from '../../../fixtures/demodata';
import { checkProductInCart } from '../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/CartPopupWindow';
import { addProductToCartFromPromotedProductsOnHomepage } from '../../Functions/HomepagePage';
import { clickOnSendOrderButton, fillEmailInThirdStep } from '../../Functions/orderContactInformation';
import { checkFinishOrderPageAsUnregistredCustomer } from '../../Functions/orderThankYouPage';
import {
    checkBasicInformationAndNoteInOrderDetail,
    checkBillingAdressInOrderDetail,
    checkDeliveryAdressInOrderDetail,
    checkOneItemInOrderDetail,
} from '../../Functions/orderDetail';
import {
    checkOrderSummaryWithOneItem,
    checkSelectedStoreInTransportList,
    checkTransportPrice,
    choosePayment,
    chooseTransportPersonalCollectionAndStore,
    continueToSecondStep,
    continueToThirdStep,
} from '../../Functions/orderSecondStep';
import {
    checkFinishOrderPageAsUnregistredCustomer,
    clickOnOrderDetailButtonOnThankYouPage,
} from '../../Functions/orderThankYouPage';
import {
    clickOnSendOrderButton,
    fillBillingAdressInThirdStep,
    fillCustomerInformationInThirdStep,
    fillEmailInThirdStep,
    fillInNoteInThirdStep,
} from '../../Functions/orderThirdStep';

it('Creating an order as unlogged user with one item, Personal collection and Cash', () => {
    cy.visit('/');
    cy.contains('Odmítnout vše').click();
    addProductToCartFromPromotedProductsOnHomepage(product1_catnum);
    checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
    checkProductInCart(product1_catnum, product1_name_prefix_suffix);
    cy.url().should('contain', url_cart);
    continueToSecondStep();

    // second step
    cy.url().should('contain', url_order_second_step);
    checkTransportPrice('2', free_price); // fist argument = position of transport list (start from id 0)
    chooseTransportPersonalCollectionAndStore(store1_name);
    checkSelectedStoreInTransportList(store1_name);
    choosePayment(payment1_name);
    checkOrderSummaryWithOneItem(
        product1_name_prefix_suffix,
        '1', // product qunatity
        product1_price,
        transport3_name,
        free_price,
        payment1_name,
        free_price,
        cart_total_price1,
    );
    continueToThirdStep();

    // third step
    cy.url().should('contain', url_order_third_step);
    fillEmailInThirdStep(customer1_email);
    fillCustomerInformationInThirdStep(customer1_phone, customer1_first_name, customer1_last_name);
    fillBillingAdressInThirdStep(customer1_billing_street, customer1_billing_city, customer1_billing_zip);
    fillInNoteInThirdStep(order_note1);
    checkOrderSummaryWithOneItem(
        product1_name_prefix_suffix,
        '1', // product quantity
        product1_price,
        transport3_name,
        free_price,
        payment1_name,
        free_price,
        cart_total_price1,
    );
    clickOnSendOrderButton();

    // thank you page order
    checkFinishOrderPageAsUnregistredCustomer();
    clickOnOrderDetailButtonOnThankYouPage();

    // order detail
    cy.url().should('contain', url_order_detail);
    checkBasicInformationAndNoteInOrderDetail(order_note1);
    checkBillingAdressInOrderDetail(
        customer1_first_name,
        customer1_last_name,
        customer1_email,
        customer1_phone,
        customer1_billing_street,
        customer1_billing_city,
        customer1_billing_zip,
        countryCZ,
    );
    checkDeliveryAdressInOrderDetail(
        customer1_first_name,
        customer1_last_name,
        customer1_phone,
        store1_street,
        store1_city,
        store1_postcode,
        countryCZ,
    );
    checkOneItemInOrderDetail(
        '0',
        product1_name_prefix_suffix,
        product1_price,
        '1',
        standart_rate,
        product1_price_without_vat,
        product1_price,
    );
    checkOneItemInOrderDetail('1', payment1_name, free_price, '1', zero_rate, free_price, free_price);
    checkOneItemInOrderDetail('2', transport3_name, free_price, '1', standart_rate, free_price, free_price);
});
