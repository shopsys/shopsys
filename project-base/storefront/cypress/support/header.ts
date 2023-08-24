import { link, url } from 'fixtures/demodata';

export const clickOnCategoryFromMenu = (categoryName: string) => {
    cy.get('[data-testid="layout-header-navigation"] [data-testid="layout-header-navigation-navigationitem"]')
        .contains(categoryName)
        .click();
}

export const searchProductByNameTypeEnterAndCheckResult = (productName: string, productCatnum: string) => {
    const productListSelector = '[data-testid="blocks-product-list-listeditem-' + productCatnum;

    typeToSearchInput(productName);
    cy.get('[data-testid="layout-header-search-autocomplete-popup-products"]').contains(productName);
    cy.get('[data-testid="layout-header-search-autocomplete-input"]').type('{enter}');
    cy.url().should('contain', url.search);
    cy.get('[data-testid="search-results-heading"]').contains(productName);
    cy.get(productListSelector + '-name').contains(productName);
}

export const typeToSearchInput = (searchText: string) => {
    cy.get('[data-testid="layout-header-search-autocomplete-input"]').type(searchText);
}

export const clickOnUserIconInHeader = () => {
    cy.get('[data-testid="layout-header-menuiconic-login-link-popup"]').click();
}

export const checkUserIsLoggedIn = () => {
    cy.get('[data-testid="layout-header-menuiconic-login-my-account"]').contains(link.myAccount);
}
