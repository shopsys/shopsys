// do NOT remove this file, it is needed for the parallel e2e tests to run in the others matrix group
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from '../../support';
import { TIDs } from '../../tids';
import { changeBlogArticleDynamicPartsToStaticDemodata } from '../visits/visitsSupport';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.MATRIX, SUBGROUP_INDEX);

describe('Matrix Test for blank others group visit tests with screenshots', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Matrix] should visit matrix page with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        changeBlogArticleDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'matrix page', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.banners_slider },
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.blog_preview_image },
            ],
        });
    });
});
