import { DataTestIds } from 'dataTestIds';
import { products, url } from 'fixtures/demodata';
import { checkUrl } from 'support';

export const increaseCartItemQuantityWithSpinbox = (listIndex = 0) => {
    cy.getByDataTestId([[DataTestIds.pages_cart_list_item_, listIndex], DataTestIds.forms_spinbox_increase]).click();
};

export const decreaseCartItemQuantityWithSpinbox = (listIndex = 0) => {
    cy.getByDataTestId([[DataTestIds.pages_cart_list_item_, listIndex], DataTestIds.forms_spinbox_decrease]).click();
};

export const checkCartItemTotalPrice = (priceWithCurrency: string, listIndex = 0) => {
    cy.getByDataTestId([
        [DataTestIds.pages_cart_list_item_, listIndex],
        DataTestIds.pages_cart_list_item_totalprice,
    ]).contains(priceWithCurrency);
};

export const checkCartTotalPrice = (priceWithCurrency: string) => {
    cy.getByDataTestId([DataTestIds.pages_cart_cartpreview_total]).contains(priceWithCurrency);
};

export const continueToTransportAndPaymentSelection = () => {
    cy.getByDataTestId([DataTestIds.blocks_orderaction_next]).click();
};

export const checkIfCorrectlyAddedHelloKittyToCart = () => {
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.fullName);
    cy.getByDataTestId([[DataTestIds.pages_cart_list_item_, 0], DataTestIds.pages_cart_list_item_name]).contains(
        products.helloKitty.fullName,
    );

    checkCartTotalPrice('€139.96');
    checkUrl(url.cart);
};

export const addProductToCartFromProductList = (productCatnum: number) => {
    cy.getByDataTestId([
        [DataTestIds.blocks_product_list_listeditem_, productCatnum],
        DataTestIds.blocks_product_addtocart,
    ])
        .should('be.visible')
        .click();
};

export const addProductToCartFromPromotedProductsOnHomepage = (productCatnum: number) => {
    cy.getByDataTestId([
        DataTestIds.blocks_product_slider_promoted_products,
        [DataTestIds.blocks_product_list_listeditem_, productCatnum],
        DataTestIds.blocks_product_addtocart,
    ]).click();
};

export const searchProductByNameTypeEnterAndCheckResult = (productName: string, productCatnum: number) => {
    cy.getByDataTestId([DataTestIds.layout_header_search_autocomplete_input]).type(productName);
    cy.getByDataTestId([DataTestIds.layout_header_search_autocomplete_popup_products]).contains(productName);
    cy.getByDataTestId([DataTestIds.layout_header_search_autocomplete_input]).type('{enter}');
    checkUrl(url.search);
    cy.getByDataTestId([DataTestIds.search_results_heading]).contains(productName);
    cy.getByDataTestId([[DataTestIds.blocks_product_list_listeditem_, productCatnum]]).contains(productName);
};

export const checkProductAndGoToCartFromCartPopupWindow = (productName: string) => {
    cy.getByDataTestId([DataTestIds.layout_popup, DataTestIds.blocks_product_addtocartpopup_product_name]).contains(
        productName,
    );
    cy.getByDataTestId([DataTestIds.layout_popup, DataTestIds.basic_link]).click();
};
