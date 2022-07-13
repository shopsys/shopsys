/// <reference types="Cypress" />
import {
    cart_total_price1,
    customer1_billing_city,
    customer1_billing_street,
    customer1_billing_zip,
    customer1_email,
    customer1_first_name,
    customer1_last_name,
    customer1_phone,
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
import { clickOnSendOrderButton, fillEmailInThirdStep } from '../../Functions/orderContactInformation';
import { checkFinishOrderPageAsUnregistredCustomer } from '../../Functions/orderThankYouPage';
import {
    checkOrderSummaryWithOneItem,
    checkSelectedStoreInTransportList,
    checkTransportPrice,
    choosePayment,
    chooseTransportPersonalCollectionAndStore,
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

it('Creating an order as unlogged user with one item, Personall collection and Cash', () => {
    cy.visit('/');
    addProductToCartFromPromotedProductsOnHomepage(product1_catnum);
    checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
    checkProductInCart(product1_catnum, product1_name_prefix_suffix);
    cy.url().should('contain', url_cart);
    continueToSecondStep();
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
    cy.url().should('contain', url_order_third_step);
    fillEmailInThirdStep(customer1_email);
    fillCustomerInformationInThirdStep(customer1_phone, customer1_first_name, customer1_last_name);
    fillBillingAdressInThirdStep(customer1_billing_street, customer1_billing_city, customer1_billing_zip);
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
    checkFinishOrderPageAsUnregistredCustomer(customer1_email);
    clickOnOrderDetailButton();
});
