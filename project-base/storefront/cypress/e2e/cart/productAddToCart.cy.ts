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

describe('Product add to cart tests', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('should add product to cart from brand list', () => {
        cy.visit(url.brandsOverwiev);
        cy.getByDataTestId('blocks-simplenavigation-22').contains(brandSencor).should('be.visible').click();
        addProductToCartFromProductList(products.helloKitty.catnum);

        checkIfCorrectlyAddedHelloKittyToCart();
    });

    it('should add product to cart from product detail', () => {
        cy.visit(products.helloKitty.url);
        cy.getByDataTestId('pages-productdetail-addtocart-button').click();

        checkIfCorrectlyAddedHelloKittyToCart();
    });

    it('should add product to cart from product list', () => {
        cy.visit(url.categoryElectronics);
        addProductToCartFromProductList(products.helloKitty.catnum);

        checkIfCorrectlyAddedHelloKittyToCart();
    });

    it('should add variant product to cart from product detail', () => {
        cy.visit(products.philips32PFL4308.url);
        cy.getByDataTestId([
            `pages-productdetail-variant-${products.philips54CRT.catnum}`,
            'blocks-product-addtocart',
        ]).click();

        checkProductAndGoToCartFromCartPopupWindow(products.philips54CRT.name);
        cy.getByDataTestId(['pages-cart-list-item-0', 'pages-cart-list-item-name']).contains(
            products.philips54CRT.name,
        );
        checkCartTotalPrice('€492.40');
        checkUrl(url.cart);
    });

    it('should add product to cart from promoted products on homepage', () => {
        cy.visit('/');
        addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);

        checkIfCorrectlyAddedHelloKittyToCart();
    });

    it('should add product to cart from search results list', () => {
        cy.visit('/');
        searchProductByNameTypeEnterAndCheckResult(products.helloKitty.name, products.helloKitty.catnum);
        addProductToCartFromProductList(products.helloKitty.catnum);

        checkIfCorrectlyAddedHelloKittyToCart();
    });
});
