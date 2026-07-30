import {
    changeArticleDynamicPartsToStaticDemodata,
    changeBlogArticleDynamicPartsToStaticDemodata,
    markArticleMapIframeForBlackout,
} from './visitsSupport';
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
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.VISITS, SUBGROUP_INDEX);

describe('Simple page visit tests with screenshots', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Homepage] should visit homepage with screenshot', () => {
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

    it('[Product Detail] should visit product detail with screenshot', () => {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'product detail', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.product_detail_main_image },
                { tid: TIDs.product_gallery_image },
                { tid: TIDs.product_gallery_video },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Category Detail] should visit category detail with screenshot', () => {
        visitEntityByUuid('category', staticData.categories.personalComputers.uuid);
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

    it('[Blog Detail] should visit blog article detail with screenshot', () => {
        visitEntityByUuid('blogArticle', staticData.blogArticle.grapesJs.uuid);
        changeBlogArticleDynamicPartsToStaticDemodata();
        cy.getByTID([TIDs.grapesjs_product_hero])
            .should('be.visible')
            .and('have.attr', 'data-product-catnum', staticData.products.philips32PFL4308.catnum);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'blog article detail', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Article Detail] should visit article detail with product hero and screenshot', () => {
        visitEntityByUuid('article', staticData.article.forPress.uuid);
        changeArticleDynamicPartsToStaticDemodata();
        markArticleMapIframeForBlackout();
        cy.getByTID([TIDs.grapesjs_product_hero]).should('be.visible');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'article detail', {
            blackout: [
                { tid: TIDs.product_list_item_image },
                { tid: TIDs.article_map_iframe },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });
});
