// do NOT remove this file, it is needed for the parallel e2e tests to run in the others matrix group
import { initializePersistStoreInLocalStorageToDefaultValues, takeSnapshotAndCompare } from '../../support';
import { TIDs } from '../../tids';
import { changeBlogArticleDynamicPartsToStaticDemodata } from '../visits/visitsSupport';

describe('Matrix Test for blank others group visit tests with screenshots', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Matrix] matrix page visit with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        changeBlogArticleDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'matrix page', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.banners_slider },
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.blog_preview_image },
            ],
        });
    });
});
