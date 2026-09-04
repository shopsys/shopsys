import { changeExpectedDeliveryDateMessagesToStaticDemodata } from '../transportAndPayment/transportAndPaymentSupport';
import {
    changeDayOfWeekInProductDeliveryStoresApiResponse,
    checkDeliveryOptionsPanelsAreNotPresent,
    checkDeliveryOptionsPanelsAreVisible,
    checkVariantSelectShowsProductName,
    chooseVariantInDeliveryOptionsPopup,
    openDeliveryOptionsPopupUsingLink,
    openDeliveryOptionsPopupUsingVariantAvailability,
} from './deliveryOptionsSupport';
import { staticData } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.DELIVERY_OPTIONS, SUBGROUP_INDEX);

describe('Delivery Options Popup Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Regular Product] should open the delivery options popup without a variant select', () => {
        changeDayOfWeekInProductDeliveryStoresApiResponse(1);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        openDeliveryOptionsPopupUsingLink();

        cy.getByTID([TIDs.delivery_options_variant_select]).should('not.exist');
        checkDeliveryOptionsPanelsAreVisible();

        changeExpectedDeliveryDateMessagesToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'popup for a regular product', {
            capture: TIDs.layout_popup,
            blackout: [{ tid: TIDs.transport_and_payment_list_item_image }, { tid: TIDs.store_opening_status }],
        });
    });

    it('[Main Variant] should require choosing a variant and load its delivery options after the choice', () => {
        changeDayOfWeekInProductDeliveryStoresApiResponse(1);
        visitEntityByUuid('product', staticData.products.televisionPhilipsM.uuid);
        openDeliveryOptionsPopupUsingLink();

        cy.getByTID([TIDs.delivery_options_variant_select]).should('be.visible');
        checkDeliveryOptionsPanelsAreNotPresent();

        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'popup with an unselected variant select', {
            capture: TIDs.layout_popup,
        });

        chooseVariantInDeliveryOptionsPopup(staticData.products.philips54CRT.uuid);
        checkVariantSelectShowsProductName(staticData.products.philips54CRT.name);
        checkDeliveryOptionsPanelsAreVisible();
    });

    it('[Main Variant] should open delivery options with the clicked variant preselected', () => {
        visitEntityByUuid('product', staticData.products.televisionPhilipsM.uuid);

        openDeliveryOptionsPopupUsingVariantAvailability(staticData.products.philips54CRT.catnum);
        checkVariantSelectShowsProductName(staticData.products.philips54CRT.name);
        checkDeliveryOptionsPanelsAreVisible();
    });
});
