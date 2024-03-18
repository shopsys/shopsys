import {
    addProductToCartFromProductList,
    checkIfCorrectlyAddedHelloKittyToCart,
    checkProductAndGoToCartFromCartPopupWindow,
    addProductToCartFromPromotedProductsOnHomepage,
    searchProductByNameTypeEnterAndCheckResult,
    checkCartTotalPrice,
} from './cartSupport';
import { brandSencor, DEFAULT_APP_STORE, products, url } from 'fixtures/demodata';
import { checkUrl } from 'support';
import { TIDs } from 'tids';

describe('Product add to cart tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should add product to cart from brand list', () => {
        cy.visitAndWaitForStableDOM(url.brandsOverwiev);
        cy.getByTID([[TIDs.blocks_simplenavigation_, 22]])
            .contains(brandSencor)
            .should('be.visible')
            .click();
        addProductToCartFromProductList(products.helloKitty.catnum);

        checkIfCorrectlyAddedHelloKittyToCart();
    });

    it('should add product to cart from product detail', () => {
        cy.visitAndWaitForStableDOM(products.helloKitty.url);
        cy.getByTID([TIDs.pages_productdetail_addtocart_button]).click();

        checkIfCorrectlyAddedHelloKittyToCart();
    });

    it('should add product to cart from product list', () => {
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        addProductToCartFromProductList(products.helloKitty.catnum);

        checkIfCorrectlyAddedHelloKittyToCart();
    });

    it('should add variant product to cart from product detail', () => {
        cy.visitAndWaitForStableDOM(products.philips32PFL4308.url);
        cy.getByTID([
            [TIDs.pages_productdetail_variant_, products.philips54CRT.catnum],
            TIDs.blocks_product_addtocart,
        ]).click();

        checkProductAndGoToCartFromCartPopupWindow(products.philips54CRT.name);
        cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.pages_cart_list_item_name]).contains(
            products.philips54CRT.name,
        );
        checkCartTotalPrice('€492.40');
        checkUrl(url.cart);
    });

    it('should add product to cart from promoted products on homepage', () => {
        cy.visitAndWaitForStableDOM('/');
        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);

        checkIfCorrectlyAddedHelloKittyToCart();
    });

    it('should add product to cart from search results list', () => {
        cy.visitAndWaitForStableDOM('/');
        searchProductByNameTypeEnterAndCheckResult(products.helloKitty.name, products.helloKitty.catnum);
        addProductToCartFromProductList(products.helloKitty.catnum);

        checkIfCorrectlyAddedHelloKittyToCart();
    });
});
