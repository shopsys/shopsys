import { products, url } from 'fixtures/demodata';
import { checkUrl } from 'support';
import { TIDs } from 'tids';

export const increaseCartItemQuantityWithSpinbox = (listIndex = 0) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, listIndex], TIDs.forms_spinbox_increase]).click();
};

export const decreaseCartItemQuantityWithSpinbox = (listIndex = 0) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, listIndex], TIDs.forms_spinbox_decrease]).click();
};

export const checkCartItemTotalPrice = (priceWithCurrency: string, listIndex = 0) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, listIndex], TIDs.pages_cart_list_item_totalprice]).contains(
        priceWithCurrency,
    );
};

export const checkCartTotalPrice = (priceWithCurrency: string) => {
    cy.getByTID([TIDs.pages_cart_cartpreview_total]).contains(priceWithCurrency);
};

export const continueToTransportAndPaymentSelection = () => {
    cy.getByTID([TIDs.blocks_orderaction_next]).click();
};

export const checkIfCorrectlyAddedHelloKittyToCart = () => {
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.fullName);
    cy.getByTID([[TIDs.pages_cart_list_item_, 0], TIDs.pages_cart_list_item_name]).contains(
        products.helloKitty.fullName,
    );

    checkCartTotalPrice('€139.96');
    checkUrl(url.cart);
};

export const addProductToCartFromProductList = (productCatnum: number) => {
    cy.getByTID([[TIDs.blocks_product_list_listeditem_, productCatnum], TIDs.blocks_product_addtocart])
        .should('be.visible')
        .click();
};

export const addProductToCartFromPromotedProductsOnHomepage = (productCatnum: number) => {
    cy.getByTID([
        TIDs.blocks_product_slider_promoted_products,
        [TIDs.blocks_product_list_listeditem_, productCatnum],
        TIDs.blocks_product_addtocart,
    ]).click();
};

export const searchProductByNameTypeEnterAndCheckResult = (productName: string, productCatnum: number) => {
    cy.getByTID([TIDs.layout_header_search_autocomplete_input]).type(productName);
    cy.getByTID([TIDs.layout_header_search_autocomplete_popup_products]).contains(productName);
    cy.getByTID([TIDs.layout_header_search_autocomplete_input]).type('{enter}');
    checkUrl(url.search);
    cy.getByTID([TIDs.search_results_heading]).contains(productName);
    cy.getByTID([[TIDs.blocks_product_list_listeditem_, productCatnum]]).contains(productName);
};

export const checkProductAndGoToCartFromCartPopupWindow = (productName: string) => {
    cy.getByTID([TIDs.layout_popup, TIDs.blocks_product_addtocartpopup_product_name]).contains(productName);
    cy.getByTID([TIDs.layout_popup, TIDs.basic_link]).click();
};
