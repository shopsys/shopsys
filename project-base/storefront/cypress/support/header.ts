import { link, url } from 'fixtures/demodata';

export const clickOnCategoryFromMenu = (categoryName: string) => {
    cy.getByDataTestId(['layout-header-navigation', 'layout-header-navigation-navigationitem'])
        .contains(categoryName)
        .click();
};

export const searchProductByNameTypeEnterAndCheckResult = (productName: string, productCatnum: string) => {
    typeToSearchInput(productName);
    cy.getByDataTestId('layout-header-search-autocomplete-popup-products').contains(productName);
    cy.getByDataTestId('layout-header-search-autocomplete-input').type('{enter}');
    cy.url().should('contain', url.search);
    cy.getByDataTestId('search-results-heading').contains(productName);
    cy.getByDataTestId('blocks-product-list-listeditem-' + productCatnum).contains(productName);
};

export const typeToSearchInput = (searchText: string) => {
    cy.getByDataTestId('layout-header-search-autocomplete-input').type(searchText);
};

export const clickOnUserIconInHeader = () => {
    cy.getByDataTestId('layout-header-menuiconic-login-link-popup').click();
};

export const checkUserIsLoggedIn = () => {
    cy.getByDataTestId('layout-header-menuiconic-login-my-account').contains(link.myAccount);
};
