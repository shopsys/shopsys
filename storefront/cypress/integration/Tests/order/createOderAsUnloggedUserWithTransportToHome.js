/// <reference types="Cypress" />
import {
    cart_total_price3,
    customer1_billing_city,
    customer1_billing_street,
    customer1_billing_zip,
    customer1_email,
    customer1_first_name,
    customer1_last_name,
    customer1_phone,
    payment2_name,
    payment2_price,
    product1_catnum,
    product1_name_prefix_suffix,
    product1_price,
    transport1_name,
    transport1_price,
    url_cart,
    url_order_second_step,
    url_order_third_step,
} from '../../../fixtures/demodata';
import { checkProductInCart } from '../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/CartPopupWindow';
import { addProductToCartFromPromotedProductsOnHomepage } from '../../Functions/HomepagePage';
import {
    checkOrderSummaryWithOneItem,
    checkTransportPrice,
    choosePayment,
    chooseTransportToHome,
    continueToSecondStep,
    continueToThirdStep,
} from '../../Functions/orderSecondStep';
import { checkFinishOrderPageAsUnregistredCustomer, clickOnOrderDetailButton } from '../../Functions/orderThankYouPage';
import {
    clickOnSendOrderButton,
    fillBillingAdressInThirdStep,
    fillCustomerInformationInThirdStep,
    fillEmailInThirdStep,
} from '../../Functions/orderThirdStep';

it('Creating an order as unlogged user with one item, Czech post and cash on delivery', () => {
    cy.visit('/');
    addProductToCartFromPromotedProductsOnHomepage(product1_catnum);
    checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
    checkProductInCart(product1_catnum, product1_name_prefix_suffix);
    cy.url().should('contain', url_cart);
    continueToSecondStep();
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
    cy.url().should('contain', url_order_third_step);
    fillEmailInThirdStep(customer1_email);
    fillCustomerInformationInThirdStep(customer1_phone, customer1_first_name, customer1_last_name);
    fillBillingAdressInThirdStep(customer1_billing_street, customer1_billing_city, customer1_billing_zip);
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
    checkFinishOrderPageAsUnregistredCustomer(customer1_email);
    clickOnOrderDetailButton();
});
