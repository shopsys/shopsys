import {
    checkEmailGiftVoucherInfoIsShownInsteadOfTransportList,
    checkGiftVoucherIsAppliedInCart,
    checkGiftVoucherIsNotAppliedInCart,
    removeGiftVoucherOnCartPage,
} from './giftVouchersSupport';
import { applyCodeOnCartPage, clickOnPromoCodeButton, goToNextOrderStep } from '../cart/cartSupport';
import {
    changeOrderConfirmationDynamicPartsToStaticDemodata,
    clickOnSendOrderButton,
    fillBillingAdressInThirdStep,
    fillCustomerInformationInThirdStep,
    fillEmailInThirdStep,
    repeatOrderFromOrderList,
} from '../order/orderSupport';
import { staticData, url } from 'fixtures/demodata';
import { generateCreateOrderInput, generateCustomerRegistrationData } from 'fixtures/generators';
import {
    checkAndHideSuccessToast,
    checkUrl,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
    translations,
} from 'support';
import { t } from 'support/translations';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.GIFT_VOUCHERS, SUBGROUP_INDEX);

describe('Gift Voucher Tests (SSP-4184)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Redeem In Cart] should apply a gift voucher in the cart, show remaining to pay, and remove the voucher again', () => {
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);

        clickOnPromoCodeButton();
        applyCodeOnCartPage(staticData.giftVouchers.unredeemed1000);
        checkAndHideSuccessToast(translations.toast.success.giftVoucherAdded);
        checkGiftVoucherIsAppliedInCart(staticData.giftVouchers.unredeemed1000);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart page with applied gift voucher', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        removeGiftVoucherOnCartPage();
        checkAndHideSuccessToast(translations.toast.success.giftVoucherRemoved);
        checkGiftVoucherIsNotAppliedInCart();
    });

    it('[Voucher Only Transport Step] should skip transport selection and show the email delivery info for a voucher-only cart', () => {
        cy.addProductToCartForTest(staticData.products.electronicGiftVoucher1000.uuid).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        checkEmailGiftVoucherInfoIsShownInsteadOfTransportList();
        cy.getByTID([TIDs.pages_order_payment]).should('be.visible');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'transport step with voucher-only cart', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        goToNextOrderStep();
        checkUrl(url.order.contactInformation);
        cy.getByTID([TIDs.contact_information_form]).within(() => {
            cy.get('#contact-information-form-street').should('be.visible');
            cy.get('#contact-information-form-isDeliveryAddressDifferentFromBilling').should('not.exist');
        });
    });

    it('[Restore Last Payment] should select gift voucher payment without an error when the last order used cash', () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest();
        cy.preselectTransportForTest(
            staticData.transport.personalCollection.uuid,
            staticData.transport.personalCollection.storeOstrava.uuid,
        );
        cy.preselectPaymentForTest(staticData.payment.cash.uuid);
        cy.createOrder(generateCreateOrderInput(registrationInput.email));
        cy.addProductToCartForTest(staticData.products.a4techMouse.uuid);
        cy.addGiftVoucherToCartForTest(staticData.giftVouchers.lastOrderCashMatchingCartTotal);

        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        cy.getByTID([TIDs.pages_order_paid_by_gift_voucher_info]).should('be.visible');
        cy.getByTID([TIDs.toast_error]).should('not.exist');
    });

    it('[Repeat Voucher Order] should not restore gift voucher payment when the repeated order has no voucher', () => {
        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest(staticData.products.a4techMouse.uuid);
        cy.preselectTransportForTest(staticData.transport.ppl.uuid);
        cy.preselectPaymentForTest(staticData.payment.goPayCreditCard.uuid);
        cy.addGiftVoucherToCartForTest(staticData.giftVouchers.lastOrderVoucherMatchingOrderTotal);
        cy.preselectPaymentForTest(staticData.payment.giftVoucher.uuid);
        cy.createOrder(generateCreateOrderInput(registrationInput.email));
        cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);

        repeatOrderFromOrderList();
        checkUrl(url.cart);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        cy.getByTID([TIDs.toast_error]).should('not.exist');
        cy.getByTID([TIDs.pages_order_paid_by_gift_voucher_info]).should('not.exist');
        cy.getByTID([TIDs.pages_order_payment]).should('be.visible');
        cy.getByTID([TIDs.pages_order_payment]).find('input[name="payment"]:checked').should('not.exist');
    });

    it(
        '[Full Voucher Payment] should replace the previously selected payment and create an order fully covered by a gift voucher without redirecting to the payment gateway',
        { retries: { runMode: 0 } },
        () => {
            cy.addProductToCartForTest(staticData.products.a4techMouse.uuid).then((cart) =>
                cy.storeCartUuidInLocalStorage(cart.uuid),
            );
            cy.preselectTransportForTest(staticData.transport.ppl.uuid);
            cy.preselectPaymentForTest(staticData.payment.goPayCreditCard.uuid);
            cy.addGiftVoucherToCartForTest(staticData.giftVouchers.fullPaymentMatchingOrderTotal);
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

            cy.getByTID([TIDs.pages_order_paid_by_gift_voucher_info]).should('be.visible');
            goToNextOrderStep();
            checkUrl(url.order.contactInformation);

            fillEmailInThirdStep(staticData.customer1.emailRegistered);
            fillCustomerInformationInThirdStep(
                staticData.customer1.phone,
                staticData.customer1.firstName,
                staticData.customer1.lastName,
            );
            fillBillingAdressInThirdStep(
                staticData.customer1.billingStreet,
                staticData.customer1.billingCity,
                staticData.customer1.billingPostCode,
            );
            loseFocus();

            clickOnSendOrderButton();
            cy.waitForStableAndInteractiveDOM();

            checkUrl(url.order.orderConfirmation);
            t('Your order was created').then((orderCreatedHeading) => {
                cy.contains(orderCreatedHeading).should('be.visible');
            });
            t('You are being redirected to the payment gateway.').then((gatewayRedirectText) => {
                cy.contains(gatewayRedirectText).should('not.exist');
            });
            changeOrderConfirmationDynamicPartsToStaticDemodata();
            takeSnapshotAndCompare(
                getSnapshotFullIndexAsString(),
                'order confirmation fully covered by gift voucher',
                {
                    blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
                },
            );
        },
    );
});
