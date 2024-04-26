import { fillInEmailAndPasswordInLoginPopup } from 'e2e/authentication/authenticationSupport';
import { buttonName, url } from 'fixtures/demodata';
import { checkUrl } from 'support';
import { TIDs } from 'tids';

export const increaseCartItemQuantityWithSpinbox = (listIndex = 0) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, listIndex], TIDs.forms_spinbox_increase]).click();
};

export const decreaseCartItemQuantityWithSpinbox = (listIndex = 0) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, listIndex], TIDs.forms_spinbox_decrease]).click();
};

export const continueToTransportAndPaymentSelection = () => {
    cy.getByTID([TIDs.blocks_orderaction_next]).click();
    cy.waitForStableDOM();
};

export const goBackToCartPage = () => {
    cy.getByTID([TIDs.blocks_orderaction_back]).click();
};

export const addProductToCartFromProductList = (productCatnum: number) => {
    cy.getByTID([[TIDs.blocks_product_list_listeditem_, productCatnum], TIDs.blocks_product_addtocart])
        .should('be.visible')
        .click();
};

export const addProductToCartFromPromotedProductsOnHomepage = (productCatnum: number | string) => {
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
    cy.getByTID([TIDs.layout_popup, TIDs.popup_go_to_cart_button]).click();
};

export const goToCartPageFromHeader = () => {
    cy.getByTID([TIDs.header_cart_link]).click();
    cy.waitForStableDOM();
};

export const goToHomepageFromHeader = () => {
    cy.getByTID([TIDs.header_homepage_link]).click();
    cy.waitForStableDOM();
};

export const checkAndCloseAddToCartPopup = () => {
    cy.getByTID([TIDs.layout_popup]).should('be.visible');
    cy.realPress('{esc}');
};

export const loginInThirdOrderStep = (password: string) => {
    cy.getByTID([TIDs.login_in_order_button]).click();
    fillInEmailAndPasswordInLoginPopup(undefined, password);
    cy.getByTID([TIDs.layout_popup]).get('button').contains(buttonName.login).click();
};
