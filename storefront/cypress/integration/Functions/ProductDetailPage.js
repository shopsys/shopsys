export function addProductToCartFromProductDetail(){
	cy.get('[data-testid="pages-productdetail-addtocart-button"]').contains('Do košíku').click()
}