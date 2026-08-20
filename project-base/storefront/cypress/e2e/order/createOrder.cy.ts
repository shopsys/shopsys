import {
    fillEmailInThirdStep,
    fillCustomerInformationInThirdStep,
    fillBillingAdressInThirdStep,
    fillInNoteInThirdStep,
    clickOnSendOrderButton,
    clickOnOrderDetailButtonOnThankYouPage,
    fillRegistrationInfoAfterOrder,
    changeOrderDetailDynamicPartsToStaticDemodata,
    changeOrderConfirmationDynamicPartsToStaticDemodata,
    submitRegistrationFormAfterOrder,
    goToOrderDetailFromOrderList,
    mouseOverUserMenuButton,
    checkOrderConfirmationStatusText,
    checkOrderDetailFromOrderPage,
    checkOrderDetailFromOrderPageWithComplaintButton,
    checkOrderDetailFromOrderPageWithPromoCode,
} from './orderSupport';
import { waitForRegistrationRedirect } from 'e2e/authentication/authenticationSupport';
import { staticData, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import { translationKeys } from 'fixtures/translationKeys';
import {
    checkAndHideSuccessToast,
    checkUrl,
    getSnapshotIndexingFunction,
    goToEditProfileFromHeader,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
    translations,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 1;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.ORDER, SUBGROUP_INDEX);

describe('Create Order Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
    });

    it('[Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail', function () {
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

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
        fillInNoteInThirdStep(staticData.orderNote);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translationKeys.order.confirmation.czechPost);

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(
            translations.transport.czechPost,
            translations.payment.onDelivery,
            staticData.orderNote,
        );
    });

    it('[Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail', function () {
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

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
        fillInNoteInThirdStep(staticData.orderNote);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translationKeys.order.confirmation.czechPost);

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(
            translations.transport.czechPost,
            translations.payment.onDelivery,
            staticData.orderNote,
        );
    });

    it('[Anon Collect Cash] should create order as unlogged user (personal collection, cash) and check it in order detail', function () {
        cy.preselectTransportForTest(
            staticData.transport.personalCollection.uuid,
            staticData.transport.personalCollection.storeOstrava.uuid,
        );
        cy.preselectPaymentForTest(staticData.payment.cash.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

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
        fillInNoteInThirdStep(staticData.orderNote);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours_in_contact_information },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.personalCollection);

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(
            `${translations.transport.personalCollection} ${staticData.transport.personalCollection.storeOstrava.name}`,
            translations.payment.cash,
            staticData.orderNote,
        );
    });

    it('[Anon PPL Card] should create order as unlogged user (PPL, credit card) and check it in order detail', function () {
        cy.preselectTransportForTest(staticData.transport.ppl.uuid);
        cy.preselectPaymentForTest(staticData.payment.creditCard.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

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
        fillInNoteInThirdStep(staticData.orderNote);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translationKeys.order.confirmation.card);

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(
            translations.transport.ppl,
            translations.payment.creditCard,
            staticData.orderNote,
        );
    });

    it('[Anon Promo Code] should create order with promo code, verify promo code summary visibility on steps 2 and 3, and check it in order detail', function () {
        cy.addPromoCodeToCartForTest(staticData.promoCode);
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);

        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);
        cy.getByTID([TIDs.pages_cart_cart_preview_total]).scrollIntoView().should('be.visible');
        cy.getByTID([TIDs.blocks_promocode_promocodeinfo_code]).should('contain.text', staticData.promoCode);

        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        cy.getByTID([TIDs.pages_cart_cart_preview_total]).scrollIntoView().should('be.visible');
        cy.getByTID([TIDs.blocks_promocode_promocodeinfo_code]).should('contain.text', staticData.promoCode);

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
        fillInNoteInThirdStep(staticData.orderNote);
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translationKeys.order.confirmation.czechPost);

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPageWithPromoCode(
            translations.transport.czechPost,
            translations.payment.onDelivery,
            staticData.orderNote,
        );
    });

    it(
        '[Register After Order] should register after order completion, and check that the just created order is in customer orders',
        { retries: { runMode: 0 } },
        function () {
            cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
            cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            fillEmailInThirdStep('after-order-registration@shopsys.com');
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
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form', {
                blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
            });

            clickOnSendOrderButton();
            cy.waitForStableAndInteractiveDOM();
            changeOrderConfirmationDynamicPartsToStaticDemodata();
            checkOrderConfirmationStatusText(translations.order.confirmation.czechPost);

            fillRegistrationInfoAfterOrder(staticData.user.password);
            submitRegistrationFormAfterOrder();
            waitForRegistrationRedirect();
            checkAndHideSuccessToast(translations.toast.success.accountCreated);
            cy.waitForStableAndInteractiveDOM();

            cy.visitAndWaitForStableAndInteractiveDOM(url.customer.orders);
            goToOrderDetailFromOrderList();
            changeOrderDetailDynamicPartsToStaticDemodata(true);
            checkOrderDetailFromOrderPageWithComplaintButton(
                translations.transport.czechPost,
                translations.payment.onDelivery,
            );

            goToEditProfileFromHeader();
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'customer edit page', {
                blackout: [
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });
        },
    );

    it(
        '[Logged Home Cash] should create order as logged-in user (transport to home, cash on delivery) and check it in order detail',
        { retries: { runMode: 0 } },
        function () {
            cy.registerAsNewUser(
                generateCustomerRegistrationData('commonCustomer', 'create-order-as-logged-in-user@shopsys.com'),
            );
            cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
            cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
            cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
            cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

            fillInNoteInThirdStep(staticData.orderNote);
            loseFocus();
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'filled contact information form', {
                blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
            });

            clickOnSendOrderButton();
            cy.waitForStableAndInteractiveDOM();
            changeOrderConfirmationDynamicPartsToStaticDemodata();
            mouseOverUserMenuButton();
            checkOrderConfirmationStatusText(translations.order.confirmation.czechPost);

            clickOnOrderDetailButtonOnThankYouPage();
            changeOrderDetailDynamicPartsToStaticDemodata();
            checkOrderDetailFromOrderPageWithComplaintButton(
                translations.transport.czechPost,
                translations.payment.onDelivery,
                staticData.orderNote,
            );
        },
    );
});
