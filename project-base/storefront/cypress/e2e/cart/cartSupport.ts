import { products, url } from 'fixtures/demodata';
import { checkUrl } from 'support';

export const increaseCartItemQuantityWithSpinbox = (listIndex = 0) => {
    cy.getByDataTestId([`pages-cart-list-item-${listIndex}`, 'forms-spinbox-increase']).click();
};

export const decreaseCartItemQuantityWithSpinbox = (listIndex = 0) => {
    cy.getByDataTestId([`pages-cart-list-item-${listIndex}`, 'forms-spinbox-decrease']).click();
};

export const checkCartItemTotalPrice = (priceWithCurrency: string, listIndex = 0) => {
    cy.getByDataTestId([`pages-cart-list-item-${listIndex}`, 'pages-cart-list-item-totalprice']).contains(
        priceWithCurrency,
    );
};

export const checkCartTotalPrice = (priceWithCurrency: string) => {
    cy.getByDataTestId('pages-cart-cartpreview-total').contains(priceWithCurrency);
};

export const continueToTransportAndPaymentSelection = () => {
    cy.getByDataTestId('blocks-orderaction-next').click();
};

export const checkIfCorrectlyAddedHelloKittyToCart = () => {
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.fullName);
    cy.getByDataTestId(['pages-cart-list-item-0', 'pages-cart-list-item-name']).contains(
        products.helloKitty.fullName,
    );

    checkCartTotalPrice('€139.96');
    checkUrl(url.cart);
};

export const addProductToCartFromProductList = (productCatnum: string) => {
    cy.getByDataTestId([`blocks-product-list-listeditem-${productCatnum}`, 'blocks-product-addtocart'])
        .should('be.visible')
        .click();
};

export const addProductToCartFromPromotedProductsOnHomepage = (productCatnum: string) => {
    cy.getByDataTestId([
        'blocks-product-slider-promoted-products',
        `blocks-product-list-listeditem-${productCatnum}`,
        'blocks-product-addtocart',
    ]).click();
};

export const searchProductByNameTypeEnterAndCheckResult = (productName: string, productCatnum: string) => {
    cy.getByDataTestId('layout-header-search-autocomplete-input').type(productName);
    cy.getByDataTestId('layout-header-search-autocomplete-popup-products').contains(productName);
    cy.getByDataTestId('layout-header-search-autocomplete-input').type('{enter}');
    checkUrl(url.search);
    cy.getByDataTestId('search-results-heading').contains(productName);
    cy.getByDataTestId('blocks-product-list-listeditem-' + productCatnum).contains(productName);
};

export const checkProductAndGoToCartFromCartPopupWindow = (productName: string) => {
    cy.getByDataTestId(['layout-popup', 'blocks-product-addtocartpopup-product-name']).contains(productName);
    cy.getByDataTestId(['layout-popup', 'basic-link-button']).click();
};
