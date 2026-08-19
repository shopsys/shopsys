import {
    addProductToComparisonFromDetail,
    addProductToComparisonFromListing,
    checkComparisonIsEmpty,
    checkComparisonProductCount,
    checkComparisonToastVisible,
    closeComparisonToast,
    removeAllFromComparison,
    removeProductFromComparison,
    visitComparisonPage,
} from './comparisonSupport';
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

    it('[Add From Listing] should add a product from category listing and show it in comparison', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        addProductToComparisonFromListing(staticData.products.helloKitty.catnum);
        checkComparisonToastVisible();
        visitComparisonPage();
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

    it('[Add And Display] should add products from listing and detail and show them in comparison', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        addProductToComparisonFromListing(staticData.products.helloKitty.catnum);
        checkComparisonToastVisible();
        closeComparisonToast();

        visitEntityByUuid('product', staticData.products.a4techMouse.uuid);
        addProductToComparisonFromDetail();
        checkComparisonToastVisible();

        visitComparisonPage();
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
        checkComparisonToastVisible();
        closeComparisonToast();

        visitEntityByUuid('product', staticData.products.a4techMouse.uuid);
        addProductToComparisonFromDetail();
        checkComparisonToastVisible();
        visitComparisonPage();

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
        checkComparisonToastVisible();
        closeComparisonToast();

        visitEntityByUuid('product', staticData.products.a4techMouse.uuid);
        addProductToComparisonFromDetail();
        checkComparisonToastVisible();
        visitComparisonPage();

        removeAllFromComparison();
        checkComparisonIsEmpty();
    });
});
