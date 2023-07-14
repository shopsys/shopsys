import { link, url } from '../../fixtures/demodata';

export function clickOnCategoryFromMenu(categoryName) {
    cy.get('[data-testid="layout-header-navigation"] [data-testid="layout-header-navigation-navigationitem"]')
        .contains(categoryName)
        .click();
}

export function searchProductByNameTypeEnterAndCheckResult(productName, productCatnum) {
    const productListSelector = '[data-testid="blocks-product-list-listeditem-' + productCatnum;

    typeToSearchInput(productName);
    cy.get('[data-testid="layout-header-search-autocomplete-products"]').contains(productName);
    cy.get('[data-testid="layout-header-search-autocomplete-input"]').type('{enter}');
    cy.url().should('contain', url.search);
    cy.get('[data-testid="basic-heading-h1"]').contains(productName);
    cy.get(productListSelector).contains(productName);
}

export function typeToSearchInput(searchText) {
    cy.get('[data-testid="layout-header-search-autocomplete-input"]').type(searchText);
}

export function clickOnUserIconInHeader() {
    cy.get('[data-testid="layout-header-menuiconic-login-link-popup"] [data-testid="basic-icon-iconsvg-User"]').click();
}

export function checkUserIsLoggedIn() {
    cy.get('[data-testid="layout-header-menuiconic-login-my-account"]').contains(link.myAccount);
}
