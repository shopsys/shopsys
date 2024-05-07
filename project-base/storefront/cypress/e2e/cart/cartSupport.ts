import { fillInEmailAndPasswordInLoginPopup } from 'e2e/authentication/authenticationSupport';
import { buttonName } from 'fixtures/demodata';
import { TIDs } from 'tids';

export const increaseCartItemQuantityWithSpinbox = (catnum: string) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, catnum], TIDs.forms_spinbox_increase]).click();
};

export const decreaseCartItemQuantityWithSpinbox = (catnum: string) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, catnum], TIDs.forms_spinbox_decrease]).click();
};

export const increaseProductListQuantityWithSpinbox = (catnum: string) => {
    cy.getByTID([[TIDs.blocks_product_list_listeditem_, catnum], TIDs.forms_spinbox_increase]).click();
};

export const decreaseProductListQuantityWithSpinbox = (catnum: string) => {
    cy.getByTID([[TIDs.blocks_product_list_listeditem_, catnum], TIDs.forms_spinbox_decrease]).click();
};

export const goToNextOrderStep = () => {
    cy.getByTID([TIDs.blocks_orderaction_next]).click();
    cy.waitForStableAndInteractiveDOM();
};

export const goToPreviousOrderStep = () => {
    cy.getByTID([TIDs.blocks_orderaction_back]).click();
    cy.waitForStableAndInteractiveDOM();
};

export const addProductToCartFromProductList = (productCatnum: string) => {
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

export const searchProductByNameWithAutocomplete = (productName: string) => {
    cy.getByTID([TIDs.layout_header_search_autocomplete_input]).type(productName);
    cy.getByTID([TIDs.layout_header_search_autocomplete_popup_products]).contains(productName);
    cy.getByTID([TIDs.layout_header_search_autocomplete_input]).type('{enter}');
};

export const goToCartPageFromHeader = () => {
    cy.getByTID([TIDs.header_cart_link]).click();
    cy.waitForStableAndInteractiveDOM();
};

export const goToHomepageFromHeader = () => {
    cy.getByTID([TIDs.header_homepage_link]).click();
    cy.waitForStableAndInteractiveDOM();
};

export const loginInThirdOrderStep = (password: string) => {
    cy.getByTID([TIDs.login_in_order_button]).click();
    fillInEmailAndPasswordInLoginPopup(undefined, password);
    cy.getByTID([TIDs.layout_popup]).get('button').contains(buttonName.login).click();
};

export const openHeaderCartByHovering = () => {
    cy.getByTID([TIDs.header_cart_link]).realHover();
};

export const removeFirstProductFromHeaderCart = () => {
    cy.getByTID([TIDs.pages_cart_removecartitembutton]).first().click();
};

export const removeProductFromCartPage = (catnum: string) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, catnum], TIDs.pages_cart_removecartitembutton]).click();
};

export const checkCartItemSpinboxDecreaseButtonIsDisabled = (catnum: string) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, catnum], TIDs.forms_spinbox_decrease]).should(
        'have.css',
        'pointer-events',
        'none',
    );
};

export const checkCartItemSpinboxIncreaseButtonIsEnabled = (catnum: string) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, catnum], TIDs.forms_spinbox_increase]).should(
        'not.have.css',
        'pointer-events',
        'none',
    );
};

export const checkCartItemSpinboxDecreaseButtonIsEnabled = (catnum: string) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, catnum], TIDs.forms_spinbox_decrease]).should(
        'not.have.css',
        'pointer-events',
        'none',
    );
};

export const checkCartItemSpinboxIncreaseButtonIsDisabled = (catnum: string) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, catnum], TIDs.forms_spinbox_increase]).should(
        'have.css',
        'pointer-events',
        'none',
    );
};

export const clickOnPromoCodeButton = () => {
    cy.getByTID([TIDs.blocks_promocode_add_button]).click();
};

export const applyPromoCodeOnCartPage = (promoCode: string) => {
    cy.get('#promoCode-form-promoCode').should('be.visible').clear({ force: true }).type(promoCode, { force: true });
    cy.getByTID([TIDs.blocks_promocode_apply_button]).click();
};

export const removePromoCodeOnCartPage = () => {
    cy.getByTID([TIDs.blocks_promocode_promocodeinfo_code]).find('svg').click();
};

export const addToCartOnProductDetailPage = () => {
    cy.getByTID([TIDs.pages_productdetail_addtocart_button]).click();
};

export const addVariantToCartFromMainVariantDetail = (catnum: string) => {
    cy.getByTID([[TIDs.pages_productdetail_variant_, catnum], TIDs.blocks_product_addtocart]).click();
};
