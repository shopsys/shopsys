import { DEFAULT_APP_STORE, transport, payment, url, products } from 'fixtures/demodata';
import { generateCustomerRegistrationData, generateCreateOrderInput, generateEmail } from 'fixtures/generators';
import { takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Order repeat tests as logged-in user from order list', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should repeat order (pre-fill cart) with initially empty cart', () => {
        const email = 'order-repeat-logged-in-with-empty-cart@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
        cy.addProductToCartForTest(products.helloKitty.uuid, 3);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email));

        cy.visitAndWaitForStableDOM(url.customer.orders);
        cy.getByTID([TIDs.order_list_repeat_order_button]).click();
        cy.waitForStableDOM();
        takeSnapshotAndCompare('order-repeat-logged-in-with-empty-cart_after-repeat');
    });

    it('should repeat order (pre-fill cart) with initially filled cart and allowed merging', () => {
        const email = 'order-repeat-logged-in-with-filled-cart-and-merging@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
        cy.addProductToCartForTest(products.helloKitty.uuid, 3);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email));

        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.addProductToCartForTest(products.lg47LA790VFHD.uuid, 2);

        cy.visitAndWaitForStableDOM(url.customer.orders);
        cy.getByTID([TIDs.order_list_repeat_order_button]).click();
        cy.getByTID([TIDs.repeat_order_merge_carts_button]).click();

        cy.waitForStableDOM();
        takeSnapshotAndCompare('order-repeat-logged-in-with-filled-cart-and-merging_after-repeat');
    });

    it('should repeat order (pre-fill cart) with initially non-empty cart and disallowed merging', () => {
        const email = 'order-repeat-logged-in-with-filled-cart-without-merging@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
        cy.addProductToCartForTest(products.helloKitty.uuid, 3);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email));

        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.addProductToCartForTest(products.lg47LA790VFHD.uuid, 2);

        cy.visitAndWaitForStableDOM(url.customer.orders);
        cy.getByTID([TIDs.order_list_repeat_order_button]).click();
        cy.getByTID([TIDs.repeat_order_dont_merge_carts_button]).click();

        cy.waitForStableDOM();
        takeSnapshotAndCompare('order-repeat-logged-in-with-filled-cart-without-merging_after-repeat');
    });
});

describe('Order repeat tests as unlogged user from order detail', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should repeat order (pre-fill cart) with initially empty cart', () => {
        const email = 'order-repeat-unlogged-with-empty-cart@shopsys.com';
        cy.addProductToCartForTest(products.helloKitty.uuid, 3).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email)).then((order) => {
            cy.visitAndWaitForStableDOM(url.order.orderDetail + `/${order.urlHash}`);
        });

        cy.getByTID([TIDs.order_detail_repeat_order_button]).click();
        cy.waitForStableDOM();
        takeSnapshotAndCompare('order-repeat-unlogged-with-empty-cart_after-repeat');
    });

    it('should repeat order (pre-fill cart) with initially filled cart and allowed merging', () => {
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

            cy.visitAndWaitForStableDOM(url.order.orderDetail + `/${order.urlHash}`);
        });

        cy.getByTID([TIDs.order_detail_repeat_order_button]).click();
        cy.getByTID([TIDs.repeat_order_merge_carts_button]).click();

        cy.waitForStableDOM();
        takeSnapshotAndCompare('order-repeat-unlogged-with-filled-cart-and-merging_after-repeat');
    });

    it('should repeat order (pre-fill cart) with initially non-empty cart and disallowed merging', () => {
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

            cy.visitAndWaitForStableDOM(url.order.orderDetail + `/${order.urlHash}`);
        });

        cy.getByTID([TIDs.order_detail_repeat_order_button]).click();
        cy.getByTID([TIDs.repeat_order_dont_merge_carts_button]).click();

        cy.waitForStableDOM();
        takeSnapshotAndCompare('order-repeat-unlogged-with-filled-cart-without-merging_after-repeat');
    });
});
