import {
    addProductToCartFromPromotedProductsOnHomepage,
    checkCartContents,
    goToCartPageFromHeader,
    goToHomepageFromHeader,
    loginInThirdOrderStep,
} from './cartSupport';
import { loginFromHeader, logoutFromHeader } from 'e2e/authentication/authenticationSupport';
import { checkEmptyCartTextIsVisible, fillEmailInThirdStep } from 'e2e/order/orderSupport';
import { staticData, url } from 'fixtures/demodata';
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
    translations,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 1;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);

describe('Cart Login Tests', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out', () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.addProductToCartForTest(staticData.products.philips32PFL4308.uuid).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);

        loginFromHeader(registrationInput.email, staticData.user.password);
        checkAndHideSuccessToast(translations.toast.success.loggedIn);
        cy.waitForStableAndInteractiveDOM();
        checkCartContents([{ product: staticData.products.philips32PFL4308, quantity: 1 }]);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(0), 'cart page after login', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToHomepageFromHeader();
        addProductToCartFromPromotedProductsOnHomepage(staticData.products.helloKitty.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();
        checkCartContents([
            { product: staticData.products.philips32PFL4308, quantity: 1 },
            { product: staticData.products.helloKitty, quantity: 1 },
        ]);

        logoutFromHeader();
        checkAndHideSuccessToast(translations.toast.success.loggedOut);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        checkEmptyCartTextIsVisible();
    });

    it('[Empty Cart] should log in, add product to an empty cart, and empty cart after log out', () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        loginFromHeader(registrationInput.email, staticData.user.password);
        checkAndHideSuccessToast(translations.toast.success.loggedIn);
        cy.waitForStableAndInteractiveDOM();

        addProductToCartFromPromotedProductsOnHomepage(staticData.products.helloKitty.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();
        checkCartContents([{ product: staticData.products.helloKitty, quantity: 1 }]);

        logoutFromHeader();
        checkAndHideSuccessToast(translations.toast.success.loggedOut);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        checkEmptyCartTextIsVisible();
    });

    it('[Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer)', () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        addProductToCartFromPromotedProductsOnHomepage(staticData.products.helloKitty.catnum);
        checkPopupIsVisible(true);

        loginFromHeader(registrationInput.email, staticData.user.password);
        checkAndHideSuccessToast(translations.toast.success.loggedIn);
        cy.waitForStableAndInteractiveDOM();

        goToCartPageFromHeader();
        checkCartContents([{ product: staticData.products.helloKitty, quantity: 1 }]);

        logoutFromHeader();
        checkAndHideSuccessToast(translations.toast.success.loggedOut);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        checkEmptyCartTextIsVisible();

        goToHomepageFromHeader();
        addProductToCartFromPromotedProductsOnHomepage(staticData.products.a4techMouse.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();

        checkCartContents([{ product: staticData.products.a4techMouse, quantity: 1 }]);
        loginFromHeader(registrationInput.email, staticData.user.password);
        checkAndHideSuccessToast(translations.toast.success.loggedIn);
        checkAndHideInfoToast(translations.toast.info.cartModified);
        cy.waitForHydration();

        checkCartContents([
            { product: staticData.products.helloKitty, quantity: 1 },
            { product: staticData.products.a4techMouse, quantity: 1 },
        ]);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        checkCartContents([
            { product: staticData.products.helloKitty, quantity: 1 },
            { product: staticData.products.a4techMouse, quantity: 1 },
        ]);
    });

    it("[Discard Cart] should discard user's previous cart after logging in in order 3rd step", () => {
        const email = 'discard-user-cart-after-login-in-order-3rd-step@shopsys.com';
        const registrationInput = generateCustomerRegistrationData('commonCustomer', email);
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest(staticData.products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);

        checkCartContents([{ product: staticData.products.philips32PFL4308, quantity: 1 }]);

        logoutFromHeader();
        checkAndHideSuccessToast(translations.toast.success.loggedOut);
        cy.waitForStableAndInteractiveDOM();
        checkIsUserLoggedOut();
        checkEmptyCartTextIsVisible();

        cy.addProductToCartForTest(staticData.products.helloKitty.uuid).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(7), 'third step before second login', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        fillEmailInThirdStep(email);
        loginInThirdOrderStep(staticData.user.password);
        checkAndHideSuccessToast(translations.toast.success.loggedIn);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(8), 'third step after second login', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
        checkCartContents([{ product: staticData.products.helloKitty, quantity: 1 }]);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        checkCartContents([{ product: staticData.products.helloKitty, quantity: 1 }]);
    });
});
