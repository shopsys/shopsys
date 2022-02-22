import { url_search } from "../../fixtures/demodata"

export function clickOnCategoryFromMenu(category_name){
	cy.get('[data-testid="layout-header-navigation"] [data-testid="layout-header-navigation-navigationitem"]').contains(category_name).click({force: true})
}

export function searchProductByNameTypeEnterAndCheckResult(product_name,product_catnum){
	const productListSelector = '[data-testid="blocks-product-list-listeditem-' + product_catnum

	typeToSearchInput(product_name)
	cy.get('[data-testid="layout-header-search-autocomplete-products"]').contains(product_name)
	cy.get('[data-testid="layout-header-search-autocomplete-input"]').type('{enter}')
	cy.url().should('contain', url_search)
	cy.get('[data-testid="basic-heading-h1"]').contains(product_name)
	cy.get(productListSelector).contains(product_name)
}

export function typeToSearchInput(search_text){
	cy.get('[data-testid="layout-header-search-autocomplete-input"]').type(search_text)
}