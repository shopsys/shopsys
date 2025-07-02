import {
    changeBlogArticleDynamicPartsToStaticDemodata,
    changeStoreOpeningHoursToStaticDemodata,
} from './visitsSupport';
import { url } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.VISITS, SUBGROUP_INDEX);

describe('Simple page visit tests with screenshots', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Homepage] should visit homepage with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        changeBlogArticleDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'homepage', {
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

    it('[Product Detail] should visit product detail with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'product detail', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.product_detail_main_image },
                { tid: TIDs.product_gallery_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Category Detail] should visit category detail with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryPersonalComputers);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'category detail', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.category_bestseller_image },
            ],
        });
    });

    it('[Stores] should visit stores page with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.stores);
        changeStoreOpeningHoursToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'stores page', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
                { tid: TIDs.stores_map },
                { tid: TIDs.store_opening_status },
            ],
        });
    });

    it('[Blog Detail] should visit blog article detail with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.blogArticleGrapesJs);
        changeBlogArticleDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'blog article detail', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
