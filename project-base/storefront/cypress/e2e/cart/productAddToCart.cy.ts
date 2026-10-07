import {
    addProductToCartFromProductList,
    addProductToCartFromPromotedProductsOnHomepage,
    addToCartOnProductDetailPage,
    addVariantToCartFromMainVariantDetail,
    checkCartContents,
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

const checkAddedProduct = (product: { uuid: string; catnum: string; name: string }) => {
    cy.wait('@addToCartMutation')
        .its('response.body.data.AddToCart.cart.items')
        .should('have.length', 1)
        .its('0')
        .should('include', { quantity: 1 })
        .its('product')
        .should('include', { uuid: product.uuid, catalogNumber: product.catnum, fullName: product.name });
    cy.getByTID([TIDs.layout_popup]).should('be.visible').and('contain.text', product.catnum);
    cy.getByTID([TIDs.layout_popup, TIDs.blocks_product_addtocartpopup_product_name])
        .should('be.visible')
        .and('have.text', product.name);
    cy.getByTID([TIDs.layout_popup, TIDs.add_to_cart_popup_quantity])
        .should('be.visible')
        .and('have.text', '1');
};

describe('Product Add To Cart Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        // Observe before the rapid-action helper's req.continue() ends request propagation.
        cy.intercept({ method: 'POST', url: '/graphql/AddToCartMutation', middleware: true }, (request) => {
            request.alias = 'addToCartMutation';
        });
    });

    it('[Brand Page Add] should add product to cart from brand page', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.brandsOverview);

        goToPageThroughSimpleNavigation(22);
        addProductToCartFromProductList(staticData.products.helloKitty.catnum);
        checkAddedProduct(staticData.products.helloKitty);
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        checkPopupIsVisible(true);
    });

    it('[Product Detail Add] should add product to cart from product detail', () => {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);

        addToCartOnProductDetailPage();
        checkAddedProduct(staticData.products.helloKitty);
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(1), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.product_detail_main_image, zIndex: 5 },
                { tid: TIDs.product_gallery_image, zIndex: 5 },
                { tid: TIDs.product_gallery_video, zIndex: 5 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Product Detail Add - Rapid Enter] should send only one AddToCart request while button is processing', () => {
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

        checkAddedProduct(staticData.products.helloKitty);
        checkPopupIsVisible(true);
    });

    it('[Cart Page Remove - Rapid Click] should send only one RemoveFromCart request when clicking rapidly', () => {
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
        checkCartContents([{ product: staticData.products.philips32PFL4308, quantity: 1 }]);
    });

    it('[Category Page Add] should add product to cart from category page', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        addProductToCartFromProductList(staticData.products.helloKitty.catnum);
        checkAddedProduct(staticData.products.helloKitty);
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(2), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [
                { tid: TIDs.add_to_cart_popup_image, zIndex: 20000 },
                { tid: TIDs.simple_navigation_image, zIndex: 9999 },
            ],
        });
        checkPopupIsVisible(true);
    });

    it('[Product Variant Add] should add variant product to cart from product detail', () => {
        visitEntityByUuid('product', staticData.products.televisionPhilipsM.uuid);

        addVariantToCartFromMainVariantDetail(staticData.products.philips100.catnum);
        checkAddedProduct(staticData.products.philips100);
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        takeSnapshotAndCompare(getSnapshotFullIndexAsString(3), 'add to cart popup', {
            capture: 'viewport',
            preserveFixed: [TIDs.layout_popup],
            blackout: [{ tid: TIDs.product_detail_main_image, zIndex: 5 }],
        });
        checkPopupIsVisible(true);
    });

    it('[Promoted Products Add] should add product to cart from promoted products on homepage', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        addProductToCartFromPromotedProductsOnHomepage(staticData.products.helloKitty.catnum);
        checkAddedProduct(staticData.products.helloKitty);
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        checkPopupIsVisible(true);
    });

    it('[Search Page Add] should add product to cart from search results page', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');

        searchProductByNameWithAutocomplete(staticData.products.helloKitty.name);
        checkUrl(`${url.search}${encodeURIComponent(staticData.products.helloKitty.name).replace(/%20/g, '+')}`);
        cy.waitForStableAndInteractiveDOM();

        addProductToCartFromProductList(staticData.products.helloKitty.catnum);
        checkAddedProduct(staticData.products.helloKitty);
        loseFocus();
        cy.waitForStableAndInteractiveDOM();
        checkPopupIsVisible(true);
    });

    it('[Product Card Quantity Adjust] should switch between add to cart button and quantity spinbox', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);

        cy.getByTID([[TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum]]).within(() => {
            cy.getByTID([TIDs.blocks_product_addtocart]).should('be.visible').click();
        });

        checkAddedProduct(staticData.products.helloKitty);
        checkPopupIsVisible(true);
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([[TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum]]).within(() => {
            cy.getByTID([TIDs.blocks_product_addtocart]).should('not.exist');
            cy.getByTID([TIDs.spinbox_input]).should('have.value', '1');
        });

        takeSnapshotAndCompare(
            getSnapshotFullIndexAsString(6),
            'product card quantity spinbox',
            {
                blackout: [
                    { tid: TIDs.product_list_item_image, zIndex: 5 },
                    { tid: TIDs.category_bestseller_image },
                    { tid: TIDs.simple_navigation_image, zIndex: 9999 },
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            },
        );

        cy.getByTID([
            [TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum],
            TIDs.forms_spinbox_increase,
        ]).click();
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.layout_popup]).should('not.exist');
        cy.getByTID([[TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum]]).within(() => {
            cy.getByTID([TIDs.spinbox_input]).should('have.value', '2');
            cy.getByTID([TIDs.forms_spinbox_decrease]).click();
        });
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([[TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum]]).within(() => {
            cy.getByTID([TIDs.spinbox_input]).should('have.value', '1');
            cy.getByTID([TIDs.forms_spinbox_decrease]).click();
        });
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([[TIDs.blocks_product_list_listeditem_, staticData.products.helloKitty.catnum]]).within(() => {
            cy.getByTID([TIDs.spinbox_input]).should('not.exist');
            cy.getByTID([TIDs.blocks_product_addtocart]).should('be.visible');
        });
    });
});
