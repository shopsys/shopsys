import {
    countryCZ,
    customer1,
    orderNote,
    payment,
    products,
    standartRate,
    totalPrice,
    transport,
    url,
} from '../../../fixtures/demodata';
import { checkProductInCart } from '../../Functions/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from '../../Functions/cartPopupWindow';
import { saveCookiesOptionsInCookiesBar } from '../../Functions/cookies';
import { addProductToCartFromPromotedProductsOnHomepage } from '../../Functions/homepage';
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
    saveCookiesOptionsInCookiesBar();
    addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    cy.url().should('contain', url.cart);
    continueToSecondStep();

    // second step
    cy.url().should('contain', url.order.secondStep);
    checkTransportPrice('0', transport.czechPost.priceWithVat); // fist argument = position of transport list (start from id 0)
    chooseTransportToHome(transport.czechPost.name);
    choosePayment(payment.onDelivery.name);
    checkOrderSummaryWithOneItem(
        products.helloKitty.namePrefixSuffix,
        '1', // item quantity
        products.helloKitty.priceWithVat,
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        totalPrice.order1,
    );
    continueToThirdStep();

    // third step
    cy.url().should('contain', url.order.thirdStep);
    fillEmailInThirdStep(customer1.email);
    fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
    fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
    fillInNoteInThirdStep(orderNote);
    checkOrderSummaryWithOneItem(
        products.helloKitty.namePrefixSuffix,
        '1', // item quantity
        products.helloKitty.priceWithVat,
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        totalPrice.order1,
    );
    clickOnSendOrderButton();

    // thank you page order
    checkFinishOrderPageAsUnregistredCustomer();
    clickOnOrderDetailButtonOnThankYouPage();

    // order detail
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
        '0', // row number
        products.helloKitty.namePrefixSuffix,
        products.helloKitty.priceWithVat,
        '1', // item qunatity
        standartRate,
        products.helloKitty.priceWithoutVat,
        products.helloKitty.priceWithVat,
    );
    checkOneItemInOrderDetail(
        '1', // row mumber
        payment.onDelivery.name,
        payment.onDelivery.priceWithVat,
        '1', // item quantity
        standartRate,
        payment.onDelivery.priceWithoutVat,
        payment.onDelivery.priceWithVat,
    );
    checkOneItemInOrderDetail(
        '2', // row number
        transport.czechPost.name,
        transport.czechPost.priceWithVat,
        '1', // item quantity
        standartRate,
        transport.czechPost.priceWithoutVat,
        transport.czechPost.priceWithVat,
    );
});
