import {
    countryCZ,
    customer1,
    freePrice,
    orderNote,
    payment,
    products,
    zeroRate,
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
    saveCookiesOptionsInCookiesBar();
    addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    cy.url().should('contain', url.cart);
    continueToSecondStep();

    // second step
    cy.url().should('contain', url.order.secondStep);
    checkTransportPrice('2', freePrice); // fist argument = position of transport list (start from id 0)
    chooseTransportPersonalCollectionAndStore(transport.personalCollection.storeOstrava.name);
    checkSelectedStoreInTransportList(transport.personalCollection.storeOstrava.name);
    choosePayment(payment.cash);
    checkOrderSummaryWithOneItem(
        products.helloKitty.namePrefixSuffix,
        '1', // product qunatity
        products.helloKitty.priceWithVat,
        transport.personalCollection.name,
        freePrice,
        payment.cash,
        freePrice,
        totalPrice.cart1,
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
        '1', // product quantity
        products.helloKitty.priceWithVat,
        transport.personalCollection.name,
        freePrice,
        payment.cash,
        freePrice,
        totalPrice.cart1,
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
        transport.personalCollection.storeOstrava.street,
        transport.personalCollection.storeOstrava.city,
        transport.personalCollection.storeOstrava.postcode,
        countryCZ,
    );
    checkOneItemInOrderDetail(
        '0', // row number
        products.helloKitty.namePrefixSuffix,
        products.helloKitty.priceWithVat,
        '1', // item quantity
        standartRate,
        products.helloKitty.priceWithoutVat,
        products.helloKitty.priceWithVat,
    );
    checkOneItemInOrderDetail('1', payment.cash, freePrice, '1', zeroRate, freePrice, freePrice);
    checkOneItemInOrderDetail(
        '2', // row number
        transport.personalCollection.name,
        freePrice,
        '1', // item quantity
        standartRate,
        freePrice,
        freePrice,
    );
});
