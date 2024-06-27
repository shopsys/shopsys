import {
    addProductToCartFromProductList,
    addProductToCartFromPromotedProductsOnHomepage,
    addToCartOnProductDetailPage,
    addVariantToCartFromMainVariantDetail,
    decreaseProductListQuantityWithSpinbox,
    increaseProductListQuantityWithSpinbox,
    searchProductByNameWithAutocomplete,
} from './cartSupport';
import { products, url } from 'fixtures/demodata';
import {
    changeProductListItemQuantityWithSpinboxInput,
    checkPopupIsVisible,
    checkUrl,
    goToPageThroughSimpleNavigation,
    initializePersistStoreInLocalStorageToDefaultValues,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

describe('Product add to cart tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should add product to cart from brand page', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.brandsOverwiev);

        goToPageThroughSimpleNavigation(22);
        addProductToCartFromProductList(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_list_item_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('should add product to cart from product detail', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(products.helloKitty.url);

        addToCartOnProductDetailPage();
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_detail_main_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('should add product to cart from category page', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);

        addProductToCartFromProductList(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('should add multiple products to cart from category page using spinbox buttons', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);

        increaseProductListQuantityWithSpinbox(products.helloKitty.catnum);
        increaseProductListQuantityWithSpinbox(products.helloKitty.catnum);
        increaseProductListQuantityWithSpinbox(products.helloKitty.catnum);
        increaseProductListQuantityWithSpinbox(products.helloKitty.catnum);
        decreaseProductListQuantityWithSpinbox(products.helloKitty.catnum);
        decreaseProductListQuantityWithSpinbox(products.helloKitty.catnum);
        addProductToCartFromProductList(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('should try to add more products to cart than allowed from category page using spinbox input, and the spinbox quantity should correct itself', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);

        changeProductListItemQuantityWithSpinboxInput(3000, products.helloKitty.catnum);
        addProductToCartFromProductList(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('should add variant product to cart from product detail', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(products.philips32PFL4308.url);

        addVariantToCartFromMainVariantDetail(products.philips54CRT.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'add to cart popup', {
            capture: 'viewport',
            blackout: [{ tid: TIDs.product_detail_main_image, zIndex: 5 }],
        });
        checkPopupIsVisible(true);
    });

    it('should add product to cart from promoted products on homepage', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.banners_slider, zIndex: 9999 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('should add product to cart from search results page', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        searchProductByNameWithAutocomplete(products.helloKitty.name);
        checkUrl(`${url.search}${encodeURIComponent(products.helloKitty.name).replace(/%20/g, '+')}`);
        cy.waitForStableAndInteractiveDOM();

        addProductToCartFromProductList(products.helloKitty.catnum);
        checkPopupIsVisible();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(this.test?.title, 'add to cart popup', {
            capture: 'viewport',
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_list_item_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });
});
