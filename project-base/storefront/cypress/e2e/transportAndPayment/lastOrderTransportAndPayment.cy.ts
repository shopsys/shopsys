import {
    changeExpectedDeliveryDateMessagesToStaticDemodata,
    changeSelectionOfPaymentByName,
    changeSelectionOfTransportByName,
    chooseTransportPersonalCollectionAndStore,
    openTransportGroupByName,
    waitForTransportAndPaymentToBeInteractive,
} from './transportAndPaymentSupport';
import { staticData, url } from 'fixtures/demodata';
import { generateCreateOrderInput, generateCustomerRegistrationData } from 'fixtures/generators';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
    translations,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.TRANSPORT_AND_PAYMENT, SUBGROUP_INDEX);

describe('Last Order Transport And Payment Select Tests', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest();
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.createOrder(generateCreateOrderInput(registrationInput.email));
        cy.addProductToCartForTest();
    });

    it('[Preselect T&P] should preselect transport and payment from last order for logged-in user', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeExpectedDeliveryDateMessagesToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'preselected transport and payment', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Change T&P And Preserve On Refresh] should change preselected transport and payment from last order for logged-in user and keep the new selection after refresh', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        changeSelectionOfTransportByName(translations.transport.czechPost, translations.transportGroup.deliveryToAddress);
        waitForTransportAndPaymentToBeInteractive();
        changeSelectionOfTransportByName(translations.transport.ppl, translations.transportGroup.deliveryToAddress);
        waitForTransportAndPaymentToBeInteractive();
        changeSelectionOfPaymentByName(translations.payment.onDelivery);
        waitForTransportAndPaymentToBeInteractive();
        cy.reloadAndWaitForStableAndInteractiveDOM();
        changeExpectedDeliveryDateMessagesToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after first change and refresh', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });

        changeSelectionOfTransportByName(translations.transport.ppl, translations.transportGroup.deliveryToAddress);
        waitForTransportAndPaymentToBeInteractive();
        chooseTransportPersonalCollectionAndStore(
            staticData.transport.personalCollection.storePardubice.uuid,
            translations.transport.personalCollection,
            translations.transportGroup.pickupPoint,
        );
        waitForTransportAndPaymentToBeInteractive();
        changeSelectionOfPaymentByName(translations.payment.cash);
        waitForTransportAndPaymentToBeInteractive();
        cy.reloadAndWaitForStableAndInteractiveDOM();
        changeExpectedDeliveryDateMessagesToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'after second change and refresh', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});

describe('Last Order Transport Overweight Tests', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();

        const registrationInput = generateCustomerRegistrationData('commonCustomer');
        cy.registerAsNewUser(registrationInput);
        cy.addProductToCartForTest();
        cy.preselectTransportForTest(staticData.transport.czechPost.uuid);
        cy.preselectPaymentForTest(staticData.payment.onDelivery.uuid);
        cy.createOrder(generateCreateOrderInput(registrationInput.email));
        cy.addProductToCartForTest(staticData.products.helloKitty.uuid);
        cy.addProductToCartForTest(staticData.products.philips32PFL4308.uuid);
    });

    it('[Last Order Overweight] should show no toast and keep Czech post unavailable when restored transport exceeds weight limit', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.order.transportAndPayment);

        cy.getByTID([TIDs.toast_info]).should('not.exist');
        cy.getByTID([TIDs.toast_error]).should('not.exist');
        openTransportGroupByName(translations.transportGroup.deliveryToAddress);
        cy.getByTID([TIDs.transport_group_panel, TIDs.pages_order_selectitem_label_name])
            .should('contain', translations.transport.ppl)
            .and('not.contain', translations.transport.czechPost);
    });
});
