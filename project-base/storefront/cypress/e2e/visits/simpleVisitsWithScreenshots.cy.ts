import {
    changeBlogArticleDynamicPartsToStaticDemodata,
    changeStoreOpeningHoursToStaticDemodata,
} from './visitsSupport';
import { url } from 'fixtures/demodata';
import { initializePersistStoreInLocalStorageToDefaultValues, takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Simple page visit tests with screenshots', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Homepage] homepage visit with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        takeSnapshotAndCompare(this.test?.title, 'homepage', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.banners_slider },
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.blog_preview_image },
            ],
        });
    });

    it('[Product Detail] product detail visit with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        takeSnapshotAndCompare(this.test?.title, 'product detail', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.product_detail_main_image },
                { tid: TIDs.product_gallery_image },
                { tid: TIDs.footer_social_links },
            ],
        });
    });

    it('[Category Detail] category detail visit with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        takeSnapshotAndCompare(this.test?.title, 'category detail', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.simple_navigation_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.category_bestseller_image },
            ],
        });
    });

    it('[Stores] stores page visit with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.stores);
        changeStoreOpeningHoursToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'stores page', {
            blackout: [{ tid: TIDs.footer_social_links }, { tid: TIDs.stores_map }, { tid: TIDs.store_opening_status }],
        });
    });

    it('[Blog Detail] blog article detail visit with screenshot', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.blogArticleGrapesJs);
        changeBlogArticleDynamicPartsToStaticDemodata();
        takeSnapshotAndCompare(this.test?.title, 'blog article detail', {
            blackout: [{ tid: TIDs.product_list_item_image }, { tid: TIDs.footer_social_links }],
        });
    });
});
