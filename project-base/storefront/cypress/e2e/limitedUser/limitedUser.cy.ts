import {
    checkAddToCartButtonNotVisible,
    checkPricesAreHidden,
    checkPricesAreVisible,
    visitB2bHomepageWithProducts,
} from './limitedUserSupport';
import { changeBlogArticleDynamicPartsToStaticDemodata } from 'e2e/visits/visitsSupport';
import { b2bUrl } from 'fixtures/demodata';
import {
    check403PageIsVisible,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loginAsB2bCatalogUser,
    loginAsB2bLimitedUser,
    loginAsB2bOwner,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.LIMITED_USER, SUBGROUP_INDEX);

describe('Limited User - Price Hiding (B2B) Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    describe('As B2B Owner (can see prices)', () => {
        beforeEach(() => {
            loginAsB2bOwner();
        });

        it('should display product prices on the category page', () => {
            visitB2bHomepageWithProducts();
            checkPricesAreVisible();
            changeBlogArticleDynamicPartsToStaticDemodata();
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'category-with-prices-owner', {
                blackout: [
                    { tid: TIDs.banners_slider },
                    { tid: TIDs.simple_navigation_image },
                    { tid: TIDs.product_list_item_image },
                    { tid: TIDs.blog_preview_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });
        });

        it('should display add to cart button', () => {
            visitB2bHomepageWithProducts();
            cy.getByTID([TIDs.blocks_product_addtocart]).should('exist');
        });
    });

    describe('As Limited User (cannot see prices)', () => {
        beforeEach(() => {
            loginAsB2bLimitedUser();
        });

        it('should not display product prices on the category page', () => {
            visitB2bHomepageWithProducts();
            checkPricesAreHidden();
            changeBlogArticleDynamicPartsToStaticDemodata();
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'category-no-prices-limited', {
                blackout: [
                    { tid: TIDs.banners_slider },
                    { tid: TIDs.simple_navigation_image },
                    { tid: TIDs.product_list_item_image },
                    { tid: TIDs.blog_preview_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });
        });

        it('should display add to cart button (limited user has order creation role)', () => {
            visitB2bHomepageWithProducts();
            cy.getByTID([TIDs.blocks_product_addtocart]).should('exist');
        });
    });

    describe('As Catalog User (cannot see prices, no cart access)', () => {
        beforeEach(() => {
            loginAsB2bCatalogUser();
        });

        it('should not display product prices on the category page', () => {
            visitB2bHomepageWithProducts();
            checkPricesAreHidden();
            changeBlogArticleDynamicPartsToStaticDemodata();
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'category-no-prices-catalog', {
                blackout: [
                    { tid: TIDs.banners_slider },
                    { tid: TIDs.simple_navigation_image },
                    { tid: TIDs.product_list_item_image },
                    { tid: TIDs.blog_preview_image },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });
        });

        it('should not display add to cart button', () => {
            visitB2bHomepageWithProducts();
            checkAddToCartButtonNotVisible();
        });

        it('should deny access to B2B cart page', () => {
            cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.cart, { failOnStatusCode: false });
            check403PageIsVisible();
        });
    });
});
