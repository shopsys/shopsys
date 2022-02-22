export function clickOnCategoryFromMenu(category_name){
	cy.get('[data-testid="layout-header-navigation"] [data-testid="layout-header-navigation-navigationitem"]').contains(category_name).click({force: true})
}