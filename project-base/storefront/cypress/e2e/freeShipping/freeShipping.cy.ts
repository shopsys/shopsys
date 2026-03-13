import { checkFreeTransportBannerShowsFree, checkFreeTransportBannerShowsRemaining } from './freeShippingSupport';
import { staticData, url } from 'fixtures/demodata';
import {
    changeCartItemQuantityWithSpinboxInput,
    checkLoaderOverlayIsNotVisibleAfterTimePeriod,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.FREE_SHIPPING, SUBGROUP_INDEX);

describe('Free Shipping Threshold Tests (SSP-1734)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Free Shipping Banner] should show free shipping progress bar in cart and reach free threshold with increased quantity', () => {
        cy.addProductToCartForTest(staticData.products.helloKitty.uuid, 1).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
        checkFreeTransportBannerShowsRemaining();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart with free shipping remaining', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        changeCartItemQuantityWithSpinboxInput(100, staticData.products.helloKitty.catnum);
        checkLoaderOverlayIsNotVisibleAfterTimePeriod(2000);
        cy.waitForStableAndInteractiveDOM();
        checkFreeTransportBannerShowsFree();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'cart with free shipping reached', {
            blackout: [
                { tid: TIDs.cart_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Free Shipping Transport Step] should show free transport options on step 2 when threshold is exceeded', () => {
        cy.addProductToCartForTest(staticData.products.helloKitty.uuid, 100).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);
        checkFreeTransportBannerShowsFree();

        cy.getByTID([TIDs.blocks_orderaction_next]).click();
        cy.waitForStableAndInteractiveDOM();
        cy.url().should('contain', url.order.transportAndPayment);
        cy.getByTID([TIDs.pages_order_transport]).should('be.visible');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'transport step with free shipping', {
            blackout: [
                { tid: TIDs.transport_and_payment_list_item_image },
                { tid: TIDs.order_summary_cart_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
