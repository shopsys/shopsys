export function addProductToCartFromProductDetail(){
	cy.get('[data-testid="pages-productdetail-addtocart-button"]').contains('Do košíku').click()
}

export function addProductVariantToCartFromProductDetail(product_catnum){
	const sliderProductVariantItemSelector = '[data-testid="pages-productdetail-variant-' + product_catnum + '"] ' + '[data-testid="blocks-product-addtocart"]'
	cy.get(sliderProductVariantItemSelector).click()
}