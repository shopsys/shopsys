import { repeatOrderFromOrderDetail, repeatOrderFromOrderList } from './orderSupport';
import { transport, payment, url, products } from 'fixtures/demodata';
import { generateCustomerRegistrationData, generateCreateOrderInput } from 'fixtures/generators';
import {
    checkUrl,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 3;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.ORDER, SUBGROUP_INDEX);

describe('Order Repeat Tests From Order List (Logged-in User)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Logged Repeat With Empty] should repeat order (pre-fill cart) for logged-in user with initially empty cart', function () {
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
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after repeat', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Logged Repeat With Prefilled And Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and allowed merging', function () {
        const email = 'order-repeat-logged-in-with-filled-cart-and-merging@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
        cy.addProductToCartForTest(products.helloKitty.uuid, 3);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email));
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.addProductToCartForTest(products.a4techMouse.uuid, 2);
        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);

        repeatOrderFromOrderList(true);
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after repeat', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Logged Repeat With Prefilled And No Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and disallowed merging', function () {
        const email = 'order-repeat-logged-in-with-filled-cart-without-merging@shopsys.com';
        cy.registerAsNewUser(generateCustomerRegistrationData('commonCustomer', email));
        cy.addProductToCartForTest(products.helloKitty.uuid, 3);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email));
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.addProductToCartForTest(products.a4techMouse.uuid, 2);
        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);

        repeatOrderFromOrderList(false);
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after repeat', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});

describe('Order Repeat Tests From Order Detail (Unlogged User)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Anon Repeat With Empty] should repeat order (pre-fill cart) for unlogged user with initially empty cart', function () {
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
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after repeat', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Anon Repeat With Prefilled Merge] should repeat order (pre-fill cart) for unlogged user with initially filled cart and allowed merging', function () {
        const email = 'order-repeat-unlogged-with-filled-cart-and-merging@shopsys.com';
        cy.addProductToCartForTest(products.helloKitty.uuid, 3).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email)).then((order) => {
            cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
            cy.addProductToCartForTest(products.a4techMouse.uuid, 2);

            cy.visitAndWaitForStableAndInteractiveDOM(url.order.orderDetail + `/${order.urlHash}`);
        });

        repeatOrderFromOrderDetail(true);
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after repeat', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Anon Repeat With Prefilled No Merge] should repeat order (pre-fill cart) for unlogged user with initially filled cart and disallowed merging', function () {
        const email = 'order-repeat-unlogged-with-filled-cart-without-merging@shopsys.com';
        cy.addProductToCartForTest(products.helloKitty.uuid, 3).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
        cy.preselectTransportForTest(transport.ppl.uuid);
        cy.preselectPaymentForTest(payment.creditCard.uuid);
        cy.createOrder(generateCreateOrderInput(email)).then((order) => {
            cy.addProductToCartForTest(products.philips32PFL4308.uuid, 4);
            cy.addProductToCartForTest(products.a4techMouse.uuid, 2);

            cy.visitAndWaitForStableAndInteractiveDOM(url.order.orderDetail + `/${order.urlHash}`);
        });

        repeatOrderFromOrderDetail(false);
        checkUrl(url.cart);
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after repeat', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
