import {
    addProductToCartFromPromotedProductsOnHomepage,
    goToCartPageFromHeader,
    goToHomepageFromHeader,
    loginInThirdOrderStep,
} from './cartSupport';
import { loginFromHeader, logoutFromHeader } from 'e2e/authentication/authenticationSupport';
import { fillEmailInThirdStep } from 'e2e/order/orderSupport';
import { password, payment, products, transport, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkAndHideInfoToast,
    checkAndHideSuccessToast,
    checkPopupIsVisible,
    initializePersistStoreInLocalStorageToDefaultValues,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Cart login tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should log in, add product to cart to an already prefilled cart, and empty cart after log out', function () {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);

        loginFromHeader(registrationInput.email, password);
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'cart page after login', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }, { tid: TIDs.footer_social_links }],
        });

        goToHomepageFromHeader();
        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();
        takeSnapshotAndCompare(this.test?.title, 'cart page after adding product to cart', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }, { tid: TIDs.footer_social_links }],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'cart page after logout', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });
    });

    it('should log in, add product to an empty cart, and empty cart after log out', function () {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        loginFromHeader(registrationInput.email, password);
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();

        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();
        takeSnapshotAndCompare(this.test?.title, 'cart page after adding product to cart', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }, { tid: TIDs.footer_social_links }],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'cart page after logout', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });
    });

    it('should repeatedly merge carts when logged in (starting with an empty cart for the registered customer)', function () {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput, false);
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkPopupIsVisible(true);

        loginFromHeader(registrationInput.email, password);
        checkAndHideSuccessToast('Successfully logged in');
        cy.waitForStableAndInteractiveDOM();

        goToCartPageFromHeader();
        takeSnapshotAndCompare(this.test?.title, 'cart page after adding product to cart', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }, { tid: TIDs.footer_social_links }],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'cart page after logout');

        goToHomepageFromHeader();
        addProductToCartFromPromotedProductsOnHomepage(products.lg47LA790VFHD.catnum);
        checkPopupIsVisible(true);
        goToCartPageFromHeader();

        takeSnapshotAndCompare(this.test?.title, 'cart page after adding second product to cart', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }, { tid: TIDs.footer_social_links }],
        });
        loginFromHeader(registrationInput.email, password);
        checkAndHideSuccessToast('Successfully logged in');
        checkAndHideInfoToast('Your cart has been modified. Please check the changes.');

        takeSnapshotAndCompare(this.test?.title, 'cart page after second login', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }, { tid: TIDs.footer_social_links }],
        });
    });

    it("should discard user's previous cart after logging in in order 3rd step", function () {
        const email = 'discard-user-cart-after-login-in-order-3rd-step@shopsys.com';
        const registrationInput = generateCustomerRegistrationData('commonCustomer', email);
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);

        takeSnapshotAndCompare(this.test?.title, 'cart page after first login', {
            blackout: [{ tid: TIDs.cart_list_item_image, shouldNotOffset: true }, { tid: TIDs.footer_social_links }],
        });

        logoutFromHeader();
        checkAndHideSuccessToast('Successfully logged out');
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'cart page after first logout', {
            blackout: [{ tid: TIDs.footer_social_links }],
        });

        cy.addProductToCartForTest(products.helloKitty.uuid).then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(transport.czechPost.uuid);
        cy.preselectPaymentForTest(payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        takeSnapshotAndCompare(this.test?.title, 'third step before second login', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });

        fillEmailInThirdStep(email);
        loginInThirdOrderStep(password);
        checkAndHideSuccessToast('Successfully logged in');
        takeSnapshotAndCompare(this.test?.title, 'third step after second login', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.order_summary_transport_and_payment_image },
            ],
        });
    });
});
