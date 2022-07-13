/// <reference types="Cypress" />
import {
    cart_total_price3,
    countryCZ,
    customer1_billing_city,
    customer1_billing_street,
    customer1_billing_zip,
    customer1_email,
    customer1_first_name,
    customer1_last_name,
    customer1_phone,
    order_note1,
    payment2_name,
    payment2_price,
    product1_catnum,
    product1_name_prefix_suffix,
    product1_price,
    product1_price_without_vat,
    standart_rate,
    transport1_name,
    transport1_price,
    transport1_price_without_vat,
    url_cart,
    url_order_detail,
    url_order_second_step,
    url_order_third_step,
    zero_rate,
} from '../../../fixtures/demodata';
import { checkProductInCart } from '../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/CartPopupWindow';
import { addProductToCartFromPromotedProductsOnHomepage } from '../../Functions/HomepagePage';
import {
    checkBasicInformationAndNoteInOrderDetail,
    checkBillingAdressInOrderDetail,
    checkDeliveryAdressInOrderDetail,
    checkOneItemInOrderDetail,
} from '../../Functions/orderDetail';
import {
    checkOrderSummaryWithOneItem,
    checkTransportPrice,
    choosePayment,
    chooseTransportToHome,
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

it('Creating an order as unlogged user with one item, Czech post and cash on delivery', () => {
    cy.visit('/');
    cy.contains('Odmítnout vše').click();
    addProductToCartFromPromotedProductsOnHomepage(product1_catnum);
    checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
    checkProductInCart(product1_catnum, product1_name_prefix_suffix);
    cy.url().should('contain', url_cart);
    continueToSecondStep();

    // second step
    cy.url().should('contain', url_order_second_step);
    checkTransportPrice('0', transport1_price); // fist argument = position of transport list (start from id 0)
    chooseTransportToHome(transport1_name);
    choosePayment(payment2_name);
    checkOrderSummaryWithOneItem(
        product1_name_prefix_suffix,
        '1', // product quantity
        product1_price,
        transport1_name,
        transport1_price,
        payment2_name,
        payment2_price,
        cart_total_price3,
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
        transport1_name,
        transport1_price,
        payment2_name,
        payment2_price,
        cart_total_price3,
    );
    clickOnSendOrderButton();

    // than you page order
    checkFinishOrderPageAsUnregistredCustomer();
    clickOnOrderDetailButtonOnThankYouPage();

    // ordet detail
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
        customer1_billing_street,
        customer1_billing_city,
        customer1_billing_zip,
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
    checkOneItemInOrderDetail('1', payment2_name, payment2_price, '1', zero_rate, payment2_price, payment2_price);
    checkOneItemInOrderDetail(
        '2',
        transport1_name,
        transport1_price,
        '1',
        standart_rate,
        transport1_price_without_vat,
        transport1_price,
    );
});
