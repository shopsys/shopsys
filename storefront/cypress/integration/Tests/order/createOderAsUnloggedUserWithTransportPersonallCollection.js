/// <reference types="Cypress" />
import {
    cart_total_price1,
    countryCZ,
    customer1,
    freePrice,
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
    cy.url().should('contain', urlOrderSecondStep);
    checkTransportPrice('2', freePrice); // fist argument = position of transport list (start from id 0)
    chooseTransportPersonalCollectionAndStore(transport.personalCollection.storeOstrava.name);
    checkSelectedStoreInTransportList(transport.personalCollection.storeOstrava.name);
    choosePayment(payment.cash);
    checkOrderSummaryWithOneItem(
        product1_name_prefix_suffix,
        '1', // product qunatity
        product1_price,
        transport.personalCollection.name,
        freePrice,
        payment.cash,
        freePrice,
        cart_total_price1,
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
        transport.personalCollection.name,
        freePrice,
        payment.cash,
        freePrice,
        cart_total_price1,
    );
    clickOnSendOrderButton();

    // thank you page order
    checkFinishOrderPageAsUnregistredCustomer();
    clickOnOrderDetailButtonOnThankYouPage();

    // order detail
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
        transport.personalCollection.storeOstrava.street,
        transport.personalCollection.storeOstrava.city,
        transport.personalCollection.storeOstrava.postcode,
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
    checkOneItemInOrderDetail('1', payment.cash, freePrice, '1', zeroRate, freePrice, freePrice);
    checkOneItemInOrderDetail(
        '2',
        transport.personalCollection.name,
        freePrice,
        '1',
        standartRate,
        freePrice,
        freePrice,
    );
});
