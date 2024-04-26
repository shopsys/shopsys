import {
    addProductToCartFromProductList,
    addProductToCartFromPromotedProductsOnHomepage,
    searchProductByNameTypeEnterAndCheckResult,
} from './cartSupport';
import { brandSencor, DEFAULT_APP_STORE, products, url } from 'fixtures/demodata';
import { takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Product add to cart tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should add product to cart from brand page', () => {
        cy.visitAndWaitForStableDOM(url.brandsOverwiev);
        cy.getByTID([[TIDs.blocks_simplenavigation_, 22]])
            .contains(brandSencor)
            .should('be.visible')
            .click();
        addProductToCartFromProductList(products.helloKitty.catnum);

        takeSnapshotAndCompare('add-product-from-product-brand-page', 'viewport');
    });

    it('should add product to cart from product detail', () => {
        cy.visitAndWaitForStableDOM(products.helloKitty.url);
        cy.getByTID([TIDs.pages_productdetail_addtocart_button]).click();

        takeSnapshotAndCompare('add-product-from-product-detail-page', 'viewport');
    });

    it('should add product to cart from category page', () => {
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        addProductToCartFromProductList(products.helloKitty.catnum);

        takeSnapshotAndCompare('add-product-from-category-page', 'viewport');
    });

    it('should add multiple products to cart from category page using spinbox buttons', () => {
        cy.visitAndWaitForStableDOM(url.categoryElectronics);

        const spinboxIncreaseButton = cy
            .getByTID([[TIDs.blocks_product_list_listeditem_, products.helloKitty.catnum], TIDs.forms_spinbox_increase])
            .should('be.visible');
        spinboxIncreaseButton.click();
        spinboxIncreaseButton.click();
        spinboxIncreaseButton.click();
        spinboxIncreaseButton.click();

        const spinboxDecreaseButton = cy
            .getByTID([[TIDs.blocks_product_list_listeditem_, products.helloKitty.catnum], TIDs.forms_spinbox_decrease])
            .should('be.visible');
        spinboxDecreaseButton.click();
        spinboxDecreaseButton.click();

        addProductToCartFromProductList(products.helloKitty.catnum);

        takeSnapshotAndCompare('add-multiple-products-from-category-page-using-spinbox-buttons', 'viewport');
    });

    it('should try to add more products to cart than allowed from category page using spinbox input, and the spinbox quantity should correct itself', () => {
        cy.visitAndWaitForStableDOM(url.categoryElectronics);

        cy.getByTID([[TIDs.blocks_product_list_listeditem_, products.helloKitty.catnum], TIDs.spinbox_input])
            .should('be.visible')
            .type('3000');

        addProductToCartFromProductList(products.helloKitty.catnum);

        takeSnapshotAndCompare('add-multiple-products-from-category-page-using-spinbox-input', 'viewport');
    });

    it('should add variant product to cart from product detail', () => {
        cy.visitAndWaitForStableDOM(products.philips32PFL4308.url);
        cy.getByTID([
            [TIDs.pages_productdetail_variant_, products.philips54CRT.catnum],
            TIDs.blocks_product_addtocart,
        ]).click();

        takeSnapshotAndCompare('add-product-from-variant-product-detail-page', 'viewport');
    });

    it('should add product to cart from promoted products on homepage', () => {
        cy.visitAndWaitForStableDOM('/');
        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);

        takeSnapshotAndCompare('add-product-from-promoted-products-on-homepage', 'viewport');
    });

    it('should add product to cart from search results page', () => {
        cy.visitAndWaitForStableDOM('/');
        searchProductByNameTypeEnterAndCheckResult(products.helloKitty.name, products.helloKitty.catnum);
        addProductToCartFromProductList(products.helloKitty.catnum);

        takeSnapshotAndCompare('add-product-from-search-results-page', 'viewport');
    });
});
