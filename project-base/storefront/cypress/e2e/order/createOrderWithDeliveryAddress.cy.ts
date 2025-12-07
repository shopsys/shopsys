import {
    clearAndFillDeliveryAdressInThirdStep,
    clearAndFillDeliveryContactInThirdStep,
    registerAndCreateOrderForDeliveryAddressTests,
    fillBillingInfoForDeliveryAddressTests,
    clickOnOrderDetailButtonOnThankYouPage,
    clickOnSendOrderButton,
    changeOrderConfirmationDynamicPartsToStaticDemodata,
    changeOrderDetailDynamicPartsToStaticDemodata,
    checkOrderConfirmationStatusText,
    checkOrderDetailFromOrderPage,
    checkOrderDetailFromOrderPageWithComplaintButton,
    selectDeliveryAddressCard,
    clickAddNewAddressButton,
    fillAndSaveNewDeliveryAddressInPopup,
} from './orderSupport';
import { staticData, url } from 'fixtures/demodata';
import { generateCustomerRegistrationData } from 'fixtures/generators';
import {
    clickOnLabel,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
    translations,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 2;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.ORDER, SUBGROUP_INDEX);

describe('Create Order With Delivery Address Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);

        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        fillBillingInfoForDeliveryAddressTests();
    });

    it('[Preserve Form On Refresh] should keep filled delivery address after page refresh', function () {
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form before filling', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clearAndFillDeliveryAdressInThirdStep(staticData.deliveryAddress);
        loseFocus();
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form after refresh', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.czechPost);

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(translations.transport.czechPost, translations.payment.onDelivery);
    });

    it('[Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again', function () {
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form before filling', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clearAndFillDeliveryAdressInThirdStep(staticData.deliveryAddress);
        loseFocus();
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        cy.wait(500);
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form after checking again', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.czechPost);

        clickOnOrderDetailButtonOnThankYouPage();
        cy.waitForStableAndInteractiveDOM();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(translations.transport.czechPost, translations.payment.onDelivery);
    });
});

describe('Delivery Address In Order Tests (Logged-in User)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Logged Popup Add Address] should add delivery address via popup for logged-in user and take snapshot after saving', function () {
        cy.registerAsNewUser(
            generateCustomerRegistrationData('commonCustomer', 'delivery-address-popup-snapshots@shopsys.com'),
        );
        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        clickAddNewAddressButton();
        fillAndSaveNewDeliveryAddressInPopup(staticData.deliveryAddress);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information with new delivery address', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.czechPost);

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPageWithComplaintButton(
            translations.transport.czechPost,
            translations.payment.onDelivery,
        );
    });

    it('[Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh', function () {
        registerAndCreateOrderForDeliveryAddressTests(
            'first-select-saved-then-fill-and-keep-filled-after-refresh@shopsys.com',
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'with default address', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickAddNewAddressButton();
        fillAndSaveNewDeliveryAddressInPopup(staticData.deliveryAddress2);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'changed contact information after refresh', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.czechPost);

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(translations.transport.czechPost, translations.payment.onDelivery);
    });

    it('[Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled', function () {
        registerAndCreateOrderForDeliveryAddressTests(
            'first-select-saved-then-change-to-new-then-to-saved-and-to-new-again-logged-in@shopsys.com',
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'with default address', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        clickAddNewAddressButton();
        fillAndSaveNewDeliveryAddressInPopup(staticData.deliveryAddress2);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'with changed delivery address', {
            blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
        });

        selectDeliveryAddressCard(0);
        loseFocus();
        selectDeliveryAddressCard(1);
        loseFocus();
        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(),
            'with changed delivery address after switching back from default',
            {
                blackout: [{ tid: TIDs.order_summary_cart_item_image }, { tid: TIDs.footer_copyright }],
            },
        );

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.czechPost);

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(translations.transport.czechPost, translations.payment.onDelivery);
    });
});

describe('Delivery Address In Order Tests (Pickup Point)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        cy.addProductToCartForTest().then((cart) => cy.storeCartUuidInLocalStorage(cart.uuid));
        cy.preselectTransportForTest(
            staticData.transport.personalCollection.uuid,
            staticData.transport.personalCollection.storeOstrava.uuid,
        );
        cy.preselectPaymentForTest(staticData.payment.cash.uuid);

        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);
        fillBillingInfoForDeliveryAddressTests();
    });

    it('[Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh', function () {
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours },
            ],
        });

        clearAndFillDeliveryContactInThirdStep(staticData.deliveryAddress);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form after refresh', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.personalCollection);

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(
            `${translations.transport.personalCollection} ${staticData.transport.personalCollection.storeOstrava.name}`,
            translations.payment.cash,
        );
    });

    it('[Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again', function () {
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours },
            ],
        });

        clearAndFillDeliveryContactInThirdStep(staticData.deliveryAddress);
        loseFocus();
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        cy.wait(500);
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after checking again', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.personalCollection);

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPage(
            `${translations.transport.personalCollection} ${staticData.transport.personalCollection.storeOstrava.name}`,
            translations.payment.cash,
        );
    });
});

describe('Delivery Address in Order Tests (Pickup Point, Logged-in User)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Logged No Prefill On Pickup Preserve On Refresh] should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then keep the filled delivery information after refresh', function () {
        registerAndCreateOrderForDeliveryAddressTests(
            'no-prefill-contact-information-with-selected-pickup-place@shopsys.com',
            staticData.transport.personalCollection.uuid,
            staticData.transport.personalCollection.storeOstrava.uuid,
            staticData.payment.cash.uuid,
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours },
            ],
        });

        clearAndFillDeliveryContactInThirdStep(staticData.deliveryAddress2);
        cy.reloadAndWaitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form after refresh', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.personalCollection);

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPageWithComplaintButton(
            `${translations.transport.personalCollection} ${staticData.transport.personalCollection.storeOstrava.name}`,
            translations.payment.cash,
        );
    });

    it('[Logged No Prefill On Pickup Preserve On Checkbox Change] should not prefill delivery contact for logged-in user with saved address and pickup point, but keep filled delivery information after unchecking and checking checkbox for different delivery address', function () {
        registerAndCreateOrderForDeliveryAddressTests(
            'keep-delivery-address-with-saved-after-uncheck@shopsys.com',
            staticData.transport.personalCollection.uuid,
            staticData.transport.personalCollection.storeOstrava.uuid,
            staticData.payment.cash.uuid,
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.contactInformation);

        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'contact information form before filling', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours },
            ],
        });

        clearAndFillDeliveryContactInThirdStep(staticData.deliveryAddress2);
        loseFocus();
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        cy.wait(500);
        clickOnLabel('contact-information-form-isDeliveryAddressDifferentFromBilling');
        loseFocus();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after checking again', {
            blackout: [
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.opening_hours },
            ],
        });

        clickOnSendOrderButton();
        cy.waitForStableAndInteractiveDOM();
        changeOrderConfirmationDynamicPartsToStaticDemodata();
        checkOrderConfirmationStatusText(translations.order.confirmation.personalCollection);

        clickOnOrderDetailButtonOnThankYouPage();
        changeOrderDetailDynamicPartsToStaticDemodata();
        checkOrderDetailFromOrderPageWithComplaintButton(
            `${translations.transport.personalCollection} ${staticData.transport.personalCollection.storeOstrava.name}`,
            translations.payment.cash,
        );
    });
});
