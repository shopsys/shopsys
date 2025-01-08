import {
    addProductToCartFromProductList,
    addProductToCartFromPromotedProductsOnHomepage,
    addToCartOnProductDetailPage,
    addVariantToCartFromMainVariantDetail,
    searchProductByNameWithAutocomplete,
} from './cartSupport';
import { products, url } from 'fixtures/demodata';
import {
    checkPopupIsVisible,
    checkUrl,
    getSnapshotIndexingFunction,
    getTestSummary,
    goToPageThroughSimpleNavigation,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 3;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);

describe('Product Add To Cart Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Brand Page Add] should add product to cart from brand page', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.visitAndWaitForStableAndInteractiveDOM(url.brandsOverview);

        goToPageThroughSimpleNavigation(22);
        addProductToCartFromProductList(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_list_item_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Product Detail Add] should add product to cart from product detail', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.visitAndWaitForStableAndInteractiveDOM(products.helloKitty.url);

        addToCartOnProductDetailPage();
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_detail_main_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Category Page Add] should add product to cart from category page', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);

        addProductToCartFromProductList(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Product Variant Add] should add variant product to cart from product detail', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.visitAndWaitForStableAndInteractiveDOM(products.philips32PFL4308.url);

        addVariantToCartFromMainVariantDetail(products.philips54CRT.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'add to cart popup', {
            capture: 'viewport',
            blackout: [{ tid: TIDs.product_detail_main_image, zIndex: 5 }],
        });
        checkPopupIsVisible(true);
    });

    it('[Promoted Products Add] should add product to cart from promoted products on homepage', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.banners_slider, zIndex: 9999 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Search Page Add] should add product to cart from search results page', function () {
        const testSummary = getTestSummary(this.test?.title);
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        searchProductByNameWithAutocomplete(products.helloKitty.name);
        checkUrl(`${url.search}${encodeURIComponent(products.helloKitty.name).replace(/%20/g, '+')}`);
        cy.waitForStableAndInteractiveDOM();

        addProductToCartFromProductList(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(testSummary), 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_list_item_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });
});
