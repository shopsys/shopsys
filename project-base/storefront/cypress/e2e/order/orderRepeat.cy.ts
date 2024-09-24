import { repeatOrderFromOrderDetail, repeatOrderFromOrderList } from './orderSupport';
import { transport, payment, url, products } from 'fixtures/demodata';
import { generateCustomerRegistrationData, generateCreateOrderInput } from 'fixtures/generators';
import { checkUrl, initializePersistStoreInLocalStorageToDefaultValues, takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Order Repeat Tests From Order List (Logged-in User)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Logged Repeat With Empty] repeat order (pre-fill cart) for logged-in user with initially empty cart', function () {
        const email = 'order-repeat-logged-in-with-empty-cart@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
        cy.addProductToCartForTest(products.helloKitty.uuid, 3);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email));
        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);

        repeatOrderFromOrderList();
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after repeat', {
            blackout: [{ tid: TIDs.cart_list_item_image }, { tid: TIDs.footer_social_links }],
        });
    });

    it('[Logged Repeat With Prefilled And Merge] repeat order (pre-fill cart) for logged-in user with initially filled cart and allowed merging', function () {
        const email = 'order-repeat-logged-in-with-filled-cart-and-merging@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
        cy.addProductToCartForTest(products.helloKitty.uuid, 3);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email));
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.addProductToCartForTest(products.lg47LA790VFHD.uuid, 2);
        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);

        repeatOrderFromOrderList(true);
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after repeat', {
            blackout: [{ tid: TIDs.cart_list_item_image }, { tid: TIDs.footer_social_links }],
        });
    });

    it('[Logged Repeat With Prefilled And No Merge] repeat order (pre-fill cart) for logged-in user with initially filled cart and disallowed merging', function () {
        const email = 'order-repeat-logged-in-with-filled-cart-without-merging@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
        cy.addProductToCartForTest(products.helloKitty.uuid, 3);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email));
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.addProductToCartForTest(products.lg47LA790VFHD.uuid, 2);
        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);

        repeatOrderFromOrderList(false);
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after repeat', {
            blackout: [{ tid: TIDs.cart_list_item_image }, { tid: TIDs.footer_social_links }],
        });
    });
});

describe('Order Repeat Tests From Order Detail (Unlogged User)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Anon Repeat With Empty] repeat order (pre-fill cart) for unlogged user with initially empty cart', function () {
        const email = 'order-repeat-unlogged-with-empty-cart@shopsys.com';
        cy.addProductToCartForTest(products.helloKitty.uuid, 3).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email)).then((order) => {
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.orderDetail + `/${order.urlHash}`);
        });

        repeatOrderFromOrderDetail();
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after repeat', {
            blackout: [{ tid: TIDs.cart_list_item_image }, { tid: TIDs.footer_social_links }],
        });
    });

    it('[Anon Repeat With Prefilled Merge] repeat order (pre-fill cart) for unlogged user with initially filled cart and allowed merging', function () {
        const email = 'order-repeat-unlogged-with-filled-cart-and-merging@shopsys.com';
        cy.addProductToCartForTest(products.helloKitty.uuid, 3).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email)).then((order) => {
            cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
            cy.addProductToCartForTest(products.lg47LA790VFHD.uuid, 2);

            cy.visitAndWaitForStableAndInteractiveDOM(url.order.orderDetail + `/${order.urlHash}`);
        });

        repeatOrderFromOrderDetail(true);
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after repeat', {
            blackout: [{ tid: TIDs.cart_list_item_image }, { tid: TIDs.footer_social_links }],
        });
    });

    it('[Anon Repeat With Prefilled No Merge] repeat order (pre-fill cart) for unlogged user with initially filled cart and disallowed merging', function () {
        const email = 'order-repeat-unlogged-with-filled-cart-without-merging@shopsys.com';
        cy.addProductToCartForTest(products.helloKitty.uuid, 3).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email)).then((order) => {
            cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
            cy.addProductToCartForTest(products.lg47LA790VFHD.uuid, 2);

            cy.visitAndWaitForStableAndInteractiveDOM(url.order.orderDetail + `/${order.urlHash}`);
        });

        repeatOrderFromOrderDetail(false);
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'after repeat', {
            blackout: [{ tid: TIDs.cart_list_item_image }, { tid: TIDs.footer_social_links }],
        });
    });
});
