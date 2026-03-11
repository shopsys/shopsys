import {
    addProductToComparisonFromDetail,
    addProductToComparisonFromListing,
    checkComparisonIsEmpty,
    checkComparisonPopupVisible,
    checkComparisonProductCount,
    closeComparisonPopup,
    goToComparisonFromPopup,
    removeAllFromComparison,
    removeProductFromComparison,
    visitComparisonPage,
} from './comparisonSupport';
import { staticData, url } from 'fixtures/demodata';
import {
    checkUrl,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.COMPARISON, SUBGROUP_INDEX);

describe('Product Comparison Tests (SSP-1719)', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Empty Comparison] should show empty comparison page', () => {
        visitComparisonPage();
        checkComparisonIsEmpty();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'empty comparison', {
            blackout: [
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Add From Listing] should add a product to comparison from category listing and navigate to comparison page', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        addProductToComparisonFromListing(staticData.products.helloKitty.catnum);
        checkComparisonPopupVisible();
        goToComparisonFromPopup();
        checkUrl(url.productComparison);
        checkComparisonProductCount(1);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'comparison with one product from listing', {
            blackout: [
                { tid: TIDs.comparison_product_image },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Add And Navigate] should add products from listing and detail, then navigate to comparison page', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        addProductToComparisonFromListing(staticData.products.helloKitty.catnum);
        checkComparisonPopupVisible();
        closeComparisonPopup();

        visitEntityByUuid('product', staticData.products.a4techMouse.uuid);
        addProductToComparisonFromDetail();
        checkComparisonPopupVisible();

        goToComparisonFromPopup();
        checkUrl(url.productComparison);
        checkComparisonProductCount(2);
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'comparison with two products', {
            blackout: [
                { tid: TIDs.comparison_product_image },
                { tid: TIDs.last_visited_products },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove Single Product] should add two products, remove one, and verify the other remains', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        addProductToComparisonFromListing(staticData.products.helloKitty.catnum);
        checkComparisonPopupVisible();
        closeComparisonPopup();

        visitEntityByUuid('product', staticData.products.a4techMouse.uuid);
        addProductToComparisonFromDetail();
        checkComparisonPopupVisible();
        goToComparisonFromPopup();

        checkComparisonProductCount(2);
        removeProductFromComparison(staticData.products.a4techMouse.catnum);
        checkComparisonProductCount(1);
        cy.getByTID([[TIDs.comparison_product_, staticData.products.helloKitty.catnum]]).should('exist');
        cy.getByTID([[TIDs.comparison_product_, staticData.products.a4techMouse.catnum]]).should('not.exist');
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'comparison after removing one product', {
            blackout: [
                { tid: TIDs.comparison_product_image },
                { tid: TIDs.last_visited_products },
                { tid: TIDs.footer_social_links },
                { tid: TIDs.footer_payment_images },
                { tid: TIDs.footer_copyright },
            ],
        });
    });

    it('[Remove All] should add products then remove all from comparison', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        addProductToComparisonFromListing(staticData.products.helloKitty.catnum);
        checkComparisonPopupVisible();
        closeComparisonPopup();

        visitEntityByUuid('product', staticData.products.a4techMouse.uuid);
        addProductToComparisonFromDetail();
        checkComparisonPopupVisible();
        goToComparisonFromPopup();

        removeAllFromComparison();
        checkComparisonIsEmpty();
    });
});
