import {
    addProductToCartFromProductList,
    addProductToCartFromPromotedProductsOnHomepage,
    addToCartOnProductDetailPage,
    addVariantToCartFromMainVariantDetail,
    searchProductByNameWithAutocomplete,
} from './cartSupport';
import { staticData, url } from 'fixtures/demodata';
import {
    checkNumberOfApiRequestsTriggeredByActions,
    checkPopupIsVisible,
    checkUrl,
    getSnapshotIndexingFunction,
    goToPageThroughSimpleNavigation,
    initializePersistStoreInLocalStorageToDefaultValues,
    loseFocus,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 3;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CART, SUBGROUP_INDEX);

describe('Product Add To Cart Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Brand Page Add] should add product to cart from brand page', function () {
        cy.visitAndWaitForStableAndInteractiveDOM(url.brandsOverview);

        goToPageThroughSimpleNavigation(22);
        addProductToCartFromProductList(staticData.products.helloKitty.catnum);
        checkPopupIsVisible();
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_list_item_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Product Detail Add] should add product to cart from product detail', function () {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);

        addToCartOnProductDetailPage();
        checkPopupIsVisible();
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_detail_main_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Product Detail Add - Rapid Enter] should send only one AddToCart request while button is processing', function () {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                cy.getByTID([TIDs.pages_productdetail_addtocart_button]).should('be.visible').focus();
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
            },
            1,
            'AddToCartMutation',
        );

        checkPopupIsVisible(true);
    });

    it('[Cart Page Remove - Rapid Click] should send only one RemoveFromCart request when clicking rapidly', function () {
        cy.addProductToCartForTest(staticData.products.helloKitty.uuid, 2).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(staticData.products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM(url.cart);

        checkNumberOfApiRequestsTriggeredByActions(
            () => {
                cy.getByTID([
                    [TIDs.pages_cart_list_item_, staticData.products.helloKitty.catnum],
                    TIDs.pages_cart_removecartitembutton,
                ])
                    .should('be.visible')
                    .focus();
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
                cy.realPress('{enter}');
            },
            1,
            'RemoveFromCartMutation',
        );
    });

    it('[Category Page Add] should add product to cart from category page', function () {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        addProductToCartFromProductList(staticData.products.helloKitty.catnum);
        checkPopupIsVisible();
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Product Variant Add] should add variant product to cart from product detail', function () {
        visitEntityByUuid('product', staticData.products.televisionPhilipsM.uuid);

        addVariantToCartFromMainVariantDetail(staticData.products.philips54CRT.catnum);
        checkPopupIsVisible();
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [{ tid: TIDs.product_detail_main_image, zIndex: 5 }],
        });
        checkPopupIsVisible(true);
    });

    it('[Promoted Products Add] should add product to cart from promoted products on homepage', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        addProductToCartFromPromotedProductsOnHomepage(staticData.products.helloKitty.catnum);
        checkPopupIsVisible();
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.banners_slider, zIndex: 9999 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Search Page Add] should add product to cart from search results page', function () {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        searchProductByNameWithAutocomplete(staticData.products.helloKitty.name);
        checkUrl(`${url.search}${encodeURIComponent(staticData.products.helloKitty.name).replace(/%20/g, '+')}`);
        cy.waitForStableAndInteractiveDOM();

        addProductToCartFromProductList(staticData.products.helloKitty.catnum);
        checkPopupIsVisible();
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_list_item_image, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });
});
