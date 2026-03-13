import {
    changeStoreOpeningHoursToStaticDemodata,
    changeStoreOpeningStatusToStaticDemodata,
    expandFirstStoreAndClickDetail,
    navigateToStoresFromHeader,
} from './storesSupport';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.STORES, SUBGROUP_INDEX);

describe('Stores Tests (SSP-1741)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Stores To Store Detail] should navigate from homepage to stores, then to store detail', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        navigateToStoresFromHeader();
        changeStoreOpeningHoursToStaticDemodata();
        changeStoreOpeningStatusToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'stores page', {
            blackout: [
                { tid: TIDs.stores_map },
                { tid: TIDs.store_opening_status },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });

        expandFirstStoreAndClickDetail();
        changeStoreOpeningStatusToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'store detail page', {
            blackout: [
                { tid: TIDs.stores_map },
                { tid: TIDs.store_opening_status },
                { tid: TIDs.opening_hours },
                { tid: TIDs.store_gallery_images },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
