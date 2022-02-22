export function productClickFromPromotedProductsOnHomepage(product_catnum, product_name){
	const sliderProductItemSelector = '[data-testid="blocks-product-sliderproductitem-' + product_catnum + '"] ' + '[data-testid="blocks-product-sliderproductitem-name"]' 
	cy.get(sliderProductItemSelector).contains(product_name).click()
}

export function addProductToCartFromPromotedProductsOnHomepage(product_catnum){
	const sliderProductItemSelector = '[data-testid="blocks-product-sliderproductitem-' + product_catnum + '"] ' + 'button[data-testid="blocks-product-addtocart"]'
	cy.get(sliderProductItemSelector).click()
}