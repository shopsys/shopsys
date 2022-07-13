/// <reference types="Cypress" />
import {
    cart_total_price1,
    free_price,
    payment1_name,
    product1_catnum,
    product1_name_prefix_suffix,
    product1_price,
    store1_name,
    transport3_name,
    url_cart,
    url_order_second_step,
    url_order_third_step,
} from '../../../fixtures/demodata';
import { checkProductInCart } from '../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/CartPopupWindow';
import { addProductToCartFromPromotedProductsOnHomepage } from '../../Functions/HomepagePage';
import {
    clickOnSendOrderButton,
    fillBillingAdressInThirdStep,
    fillCustomerInformationInThirdStep,
    fillEmailInThirdStep,
} from '../../Functions/orderContactInformation';
import {
    checkOrderSummaryWithOneItem,
    checkSelectedStoreInTransportList,
    checkTransportPrice,
    choosePayment,
    chooseTransportPersonalCollection,
    continueToSecondStep,
    continueToThirdStep,
} from '../../Functions/orderSecondStep';
import { checkFinishOrderPageAsUnregistredCustomer } from '../../Functions/orderThankYouPage';

it('Creating an order as unlogged user with one item, Personall collection and Cash', () => {
    cy.visit('/');
    addProductToCartFromPromotedProductsOnHomepage(product1_catnum);
    checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
    checkProductInCart(product1_catnum, product1_name_prefix_suffix);
    cy.url().should('contain', url_cart);
    continueToSecondStep();
    cy.url().should('contain', url_order_second_step);
    checkTransportPrice('2', free_price); // position of transport start from id 0
    chooseTransportPersonalCollection(store1_name);
    checkSelectedStoreInTransportList(store1_name);
    choosePayment(payment1_name);
    checkOrderSummaryWithOneItem(
        product1_name_prefix_suffix,
        '1',
        product1_price,
        transport3_name,
        free_price,
        payment1_name,
        free_price,
        cart_total_price1,
    );
    continueToThirdStep();
    cy.url().should('contain', url_order_third_step);
    fillEmailInThirdStep(customer1_email);
    fillCustomerInformationInThirdStep(customer1_phone, customer1_first_name, customer1_last_name);
    fillBillingAdressInThirdStep(customer1_billing_street, customer1_billing_city, customer1_billing_zip);
    checkOrderSummaryWithOneItem(
        product1_name_prefix_suffix,
        '1',
        product1_price,
        transport3_name,
        free_price,
        payment1_name,
        free_price,
        cart_total_price1,
    );
    clickOnSendOrderButton();
    checkFinishOrderPageAsUnregistredCustomer(customer1_email);
});
