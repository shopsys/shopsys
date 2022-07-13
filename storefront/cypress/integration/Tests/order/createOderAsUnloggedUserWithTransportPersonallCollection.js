/// <reference types="Cypress" />
import { product1_catnum, product1_name_prefix_suffix, url_cart } from '../../../fixtures/demodata';
import { checkProductInCart } from '../../Functions/CartPage';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/CartPopupWindow';
import { addProductToCartFromPromotedProductsOnHomepage } from '../../Functions/HomepagePage';

it('Creating an order as unlogged user with one item, Personall collection and Cash', () => {
    cy.visit('/');
    addProductToCartFromPromotedProductsOnHomepage(product1_catnum);
    checkProductAndGoToCartFromCartPopupWindow(product1_name_prefix_suffix);
    checkProductInCart(product1_catnum, product1_name_prefix_suffix);
    cy.url().should('contain', url_cart);
    continueToSecondStep();
    cy.url().should('contain', url_order_transport_and_payment);
    checkTransportPrice('2', free_price);
    chooseTransportPersonalCollection(store1_name); // selector doplnit do fce
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
    cy.url().should('contain', url_order_contact_data);
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
