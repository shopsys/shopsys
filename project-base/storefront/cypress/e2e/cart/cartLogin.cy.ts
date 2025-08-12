import {
    addProductToCartFromPromotedProductsOnHomepage,
    goToCartPageFromHeader,
    goToHomepageFromHeader,
    loginInThirdOrderStep,
} from './cartSupport';
import { loginFromHeader, logoutFromHeader } from 'e2e/authentication/authenticationSupport';
import { checkEmptyCartTextIsVisible, fillEmailInThirdStep } from 'e2e/order/orderSupport';
import { password, payment, products, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkAndHideInfoToast,
    checkAndHideSuccessToast,
    checkIsUserLoggedOut,
    checkPopupIsVisible,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 1;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);

describe('Cart Login Tests', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out', function () {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);

        loginFromHeader(registrationInput.email, password);
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after login', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToHomepageFromHeader();
        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after adding product to cart', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        checkEmptyCartTextIsVisible();
    });

    it('[Empty Cart] should log in, add product to an empty cart, and empty cart after log out', function () {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        loginFromHeader(registrationInput.email, password);
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();

        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after adding product to cart', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        checkEmptyCartTextIsVisible();
    });

    it('[Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer)', function () {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkPopupIsVisible(true);

        loginFromHeader(registrationInput.email, password);
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();
        cy.waitForHydration();

        goToCartPageFromHeader();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after adding product to cart', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        checkEmptyCartTextIsVisible();

        goToHomepageFromHeader();
        addProductToCartFromPromotedProductsOnHomepage(products.a4techMouse.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();

        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after adding second product to cart', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
        loginFromHeader(registrationInput.email, password);
        checkAndHideSuccessToast('Successfully logged in');
        checkAndHideInfoToast('Your cart has been modified. Please check the changes.');
        cy.waitForHydration();

        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after second login', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it("[Discard Cart] should discard user's previous cart after logging in in order 3rd step", function () {
        const email = 'discard-user-cart-after-login-in-order-3rd-step@shopsys.com';
        const registrationInput = generateCustomerRegistrationData('commonCustomer', email);
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);

        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page after first login', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        checkEmptyCartTextIsVisible();

        cy.addProductToCartForTest(products.helloKitty.uuid).then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'third step before second login', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        fillEmailInThirdStep(email);
        loginInThirdOrderStep(password);
        checkAndHideSuccessToast('Successfully logged in');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'third step after second login', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });
    });
});
