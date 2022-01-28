export function productClickFromPromoProductsOnHomepage(catnum, product_name){
	const sliderProductItemSelector = '[data-testid="blocks-product-sliderproductitem-' + catnum + '"] ' + '[data-testid="blocks-product-sliderproductitem-name"]' 
	cy.get(sliderProductItemSelector).contains(product_name).click()
}