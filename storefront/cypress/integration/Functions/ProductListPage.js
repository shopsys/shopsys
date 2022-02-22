export function addProductToCartFromProductList(product_catnum){ 
	const selector = '[data-testid="blocks-product-list-listeditem-' + product_catnum + '"] ' + '[data-testid="blocks-product-addtocart"]' 
	cy.get(selector).contains('Do košíku').click()
}