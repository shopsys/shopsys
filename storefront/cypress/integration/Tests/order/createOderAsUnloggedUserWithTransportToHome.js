/// <reference types="Cypress" />
import {
    cart_total_price3,
    countryCZ,
    customer1,
    orderNote,
    payment,
    product1_catnum,
    product1_name_prefix_suffix,
    product1_price,
    product1_price_without_vat,
    standartRate,
    transport,
    url_cart,
    urlOrderSecondStep,
    urlOrderThirdStep,
    zeroRate,
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
    cy.url().should('contain', urlOrderSecondStep);
    checkTransportPrice('0', transport.czechPost.priceWithVat); // fist argument = position of transport list (start from id 0)
    chooseTransportToHome(transport.czechPost.name);
    choosePayment(payment.onDelivery.name);
    checkOrderSummaryWithOneItem(
        product1_name_prefix_suffix,
        '1', // product quantity
        product1_price,
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        cart_total_price3,
    );
    continueToThirdStep();

    // third step
    cy.url().should('contain', urlOrderThirdStep);
    fillEmailInThirdStep(customer1.email);
    fillCustomerInformationInThirdStep(customer1.phone, customer1.first_name, customer1.last_name);
    fillBillingAdressInThirdStep(customer1.billing_street, customer1.billing_city, customer1.billing_zip);
    fillInNoteInThirdStep(orderNote);
    checkOrderSummaryWithOneItem(
        product1_name_prefix_suffix,
        '1', // product quantity
        product1_price,
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        cart_total_price3,
    );
    clickOnSendOrderButton();

    // than you page order
    checkFinishOrderPageAsUnregistredCustomer();
    clickOnOrderDetailButtonOnThankYouPage();

    // ordet detail
    checkBasicInformationAndNoteInOrderDetail(orderNote);
    checkBillingAdressInOrderDetail(
        customer1.first_name,
        customer1.last_name,
        customer1.email,
        customer1.phone,
        customer1.billing_street,
        customer1.billing_city,
        customer1.billing_zip,
        countryCZ,
    );
    checkDeliveryAdressInOrderDetail(
        customer1.first_name,
        customer1.last_name,
        customer1.phone,
        customer1.billing_street,
        customer1.billing_city,
        customer1.billing_zip,
        countryCZ,
    );
    checkOneItemInOrderDetail(
        '0',
        product1_name_prefix_suffix,
        product1_price,
        '1',
        standartRate,
        product1_price_without_vat,
        product1_price,
    );
    checkOneItemInOrderDetail(
        '1',
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        '1',
        zeroRate,
        payment.onDelivery.priceWithoutVat,
        payment.onDelivery.priceWithVat,
    );
    checkOneItemInOrderDetail(
        '2',
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        '1',
        standartRate,
        transport.czechPost.priceWithoutVat,
        transport.czechPost.priceWithVat,
    );
});
