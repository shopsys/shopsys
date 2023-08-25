import {
    countryCZ,
    customer1,
    orderNote,
    payment,
    products,
    zeroRate,
    standartRate,
    totalPrice,
    transport,
    url,
} from 'fixtures/demodata';
import { checkProductInCart } from 'support/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from 'support/cartPopupWindow';
import { saveCookiesOptionsInCookiesBar } from 'support/cookies';
import { addProductToCartFromPromotedProductsOnHomepage } from 'support/homepage';
import {
    checkBasicInformationAndNoteInOrderDetail,
    checkBillingAdressInOrderDetail,
    checkDeliveryAdressInOrderDetail,
    checkOneItemInOrderDetail,
} from 'support/orderDetail';
import {
    continueToSecondStep,
    checkTransportPrice,
    chooseTransportToHome,
    choosePayment,
    checkOrderSummaryWithOneItem,
    continueToThirdStep,
} from 'support/orderSecondStep';
import {
    checkFinishOrderPageAsUnregistredCustomer,
    clickOnOrderDetailButtonOnThankYouPage,
} from 'support/orderThankYouPage';
import {
    fillEmailInThirdStep,
    fillCustomerInformationInThirdStep,
    fillBillingAdressInThirdStep,
    fillInNoteInThirdStep,
    clickOnSendOrderButton,
} from 'support/orderThirdStep';

it('Creating an order as unlogged user with one item, Czech post and cash on delivery', () => {
    cy.visit('/');
    saveCookiesOptionsInCookiesBar();
    addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    cy.url().should('contain', url.cart);
    continueToSecondStep();

    cy.url().should('contain', url.order.secondStep);
    checkTransportPrice(0, transport.czechPost.priceWithVat);
    chooseTransportToHome(transport.czechPost.name);
    choosePayment(payment.onDelivery.name);
    checkOrderSummaryWithOneItem(
        products.helloKitty.namePrefixSuffix,
        1,
        products.helloKitty.priceWithVat,
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        totalPrice.order1,
    );
    continueToThirdStep();

    cy.url().should('contain', url.order.thirdStep);
    fillEmailInThirdStep(customer1.email);
    fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
    fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
    fillInNoteInThirdStep(orderNote);
    checkOrderSummaryWithOneItem(
        products.helloKitty.namePrefixSuffix,
        1,
        products.helloKitty.priceWithVat,
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        totalPrice.order1,
    );
    clickOnSendOrderButton();

    checkFinishOrderPageAsUnregistredCustomer();
    clickOnOrderDetailButtonOnThankYouPage();

    checkBasicInformationAndNoteInOrderDetail(orderNote);
    checkBillingAdressInOrderDetail(
        customer1.firstName,
        customer1.lastName,
        customer1.email,
        customer1.phone,
        customer1.billingStreet,
        customer1.billingCity,
        customer1.billingPostCode,
        countryCZ,
    );
    checkDeliveryAdressInOrderDetail(
        customer1.firstName,
        customer1.lastName,
        customer1.phone,
        customer1.billingStreet,
        customer1.billingCity,
        customer1.billingPostCode,
        countryCZ,
    );
    checkOneItemInOrderDetail(
        0,
        products.helloKitty.namePrefixSuffix,
        products.helloKitty.priceWithVat,
        1,
        standartRate,
        products.helloKitty.priceWithoutVat,
        products.helloKitty.priceWithVat,
    );
    checkOneItemInOrderDetail(
        1,
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        1,
        zeroRate,
        payment.onDelivery.priceWithoutVat,
        payment.onDelivery.priceWithVat,
    );
    checkOneItemInOrderDetail(
        2,
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        1,
        standartRate,
        transport.czechPost.priceWithoutVat,
        transport.czechPost.priceWithVat,
    );
});
