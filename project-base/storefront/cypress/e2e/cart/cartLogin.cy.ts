import {
    addProductToCartFromPromotedProductsOnHomepage,
    goToCartPageFromHeader,
    goToHomepageFromHeader,
    checkAndCloseAddToCartPopup,
    loginInThirdOrderStep,
} from './cartSupport';
import { checkUserIsLoggedIn, loginFromHeader, logoutFromHeader } from 'e2e/authentication/authenticationSupport';
import { fillEmailInThirdStep } from 'e2e/order/orderSupport';
import { DEFAULT_APP_STORE, password, payment, products, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import { checkAndHideSuccessToast, takeSnapshotAndCompare } from 'support';

describe('Cart login tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should log in, add product to cart to an already prefilled cart, and empty cart after log out', () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.visitAndWaitForStableDOM(url.cart);

        loginFromHeader(registrationInput.email, password);
        checkUserIsLoggedIn();
        checkAndHideSuccessToast();
        takeSnapshotAndCompare('cart-login-with-prefilled-cart_after-login');

        goToHomepageFromHeader();
        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkAndCloseAddToCartPopup();
        goToCartPageFromHeader();
        takeSnapshotAndCompare('cart-login-with-prefilled-cart_after-adding-product-to-cart');

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableDOM();
        takeSnapshotAndCompare('cart-login-with-prefilled-cart_after-logout');
    });

    it('should log in, add product to cart an empty cart, and empty cart after log out', () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.visitAndWaitForStableDOM('/');

        loginFromHeader(registrationInput.email, password);
        checkUserIsLoggedIn();
        checkAndHideSuccessToast();

        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkAndCloseAddToCartPopup();
        goToCartPageFromHeader();
        takeSnapshotAndCompare('cart-login-with-empty-cart_after-adding-product-to-cart');

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableDOM();
        takeSnapshotAndCompare('cart-login-with-empty-cart_after-logout');
    });

    it('should repeatedly merge carts when logged in (starting with empty cart)', () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.visitAndWaitForStableDOM('/');

        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkAndCloseAddToCartPopup();

        loginFromHeader(registrationInput.email, password);
        checkUserIsLoggedIn();
        checkAndHideSuccessToast();

        goToCartPageFromHeader();
        takeSnapshotAndCompare('cart-repeated-login-logout-with-empty-cart_after-adding-product-to-cart');

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableDOM();
        takeSnapshotAndCompare('cart-repeated-login-logout-with-empty-cart_after-logout');

        goToHomepageFromHeader();
        addProductToCartFromPromotedProductsOnHomepage(products.lg47LA790VFHD.catnum);
        checkAndCloseAddToCartPopup();
        goToCartPageFromHeader();

        takeSnapshotAndCompare('cart-repeated-login-logout-with-empty-cart_after-adding-second-product-to-cart');
        loginFromHeader(registrationInput.email, password);
        checkUserIsLoggedIn();
        checkAndHideSuccessToast();

        takeSnapshotAndCompare('cart-repeated-login-logout-with-empty-cart_after-second-login');
    });

    it("should discard user's previous cart after logging in in order 3rd step", () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.addProductToCartForTest(products.philips32PFL4308.uuid).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.registerAsNewUser(registrationInput, true);
        cy.visitAndWaitForStableDOM(url.cart);

        takeSnapshotAndCompare('cart-login-in-third-step_after-first-login');

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableDOM();
        takeSnapshotAndCompare('cart-login-in-third-step_after-first-logout');

        cy.addProductToCartForTest(products.helloKitty.uuid).then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableDOM(url.order.contactInformation);
        takeSnapshotAndCompare('cart-login-in-third-step_before-second-login');

        fillEmailInThirdStep(registrationInput.email);
        loginInThirdOrderStep(password);
        checkAndHideSuccessToast('Successfully logged in');
        takeSnapshotAndCompare('cart-login-in-third-step_after-second-login');
    });
});
