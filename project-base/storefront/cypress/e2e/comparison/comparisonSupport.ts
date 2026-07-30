import { url } from 'fixtures/demodata';
import { checkAndHideSuccessToast, checkUrl } from 'support';
import { TIDs } from 'tids';

export const visitComparisonPage = () => {
    cy.visitAndWaitForStableAndInteractiveDOM(url.productComparison);
    checkUrl(url.productComparison);
};

export const checkComparisonIsEmpty = () => {
    cy.getByTID([TIDs.comparison_empty_state]).should('be.visible');
};

export const addProductToComparisonFromListing = (catnum: string) => {
    cy.getByTID([[TIDs.blocks_product_list_listeditem_, catnum], TIDs.product_compare_button]).click({ force: true });
};

export const checkComparisonToastVisible = () => {
    cy.getByTID([TIDs.layout_popup]).should('not.exist');
    cy.getByTID([TIDs.toast_success]).should('be.visible');
};

export const closeComparisonToast = () => {
    cy.getByTID([TIDs.toast_success]).click().should('not.exist');
};

export const removeAllFromComparison = () => {
    cy.getByTID([TIDs.comparison_remove_all_button]).click();
    cy.getByTID([TIDs.layout_popup]).should('be.visible');
    cy.getByTID([TIDs.popup_confirm_button]).click();
    cy.waitForStableAndInteractiveDOM();
    checkAndHideSuccessToast();
};

export const addProductToComparisonFromDetail = () => {
    cy.getByTID([TIDs.product_compare_button]).first().click({ force: true });
};

export const removeProductFromComparison = (catnum: string) => {
    cy.getByTID([[TIDs.comparison_product_, catnum], TIDs.product_compare_button]).click({ force: true });
    cy.waitForStableAndInteractiveDOM();
    checkAndHideSuccessToast();
};

export const checkComparisonProductCount = (count: number) => {
    cy.getByTID([TIDs.page_title]).should('contain', `(${count})`);
};
