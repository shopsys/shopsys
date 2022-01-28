export function checkProductInCart(catnum, product_name){
	const cartProductItemSelector = '[data-testid="pages-cart-list-item-' + catnum + '"] ' + '[data-testid="pages-cart-list-item-iteminfo-name"]'
	const productCatnum = 'Kód' + ': ' + catnum 
	cy.get(cartProductItemSelector).contains(product_name)
	cy.get(cartProductItemSelector).contains(productCatnum) 
	cy.url().should('contain', '/kosik')
}