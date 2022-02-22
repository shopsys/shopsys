import { cart_total_price1, product1_catnum, product1_name, product1_name_prefix_suffix, product1_url_prefix_suffix, url_kosik } from "../../fixtures/demodata"
import { checkProductInCart, checkTotalPriceInCart } from "../Functions/CartPage"
import { checkProductAndGoToCartFromFloatingWindow } from "../Functions/CartPopupWindow"
import { addProductToCartFromPromotedProductsOnHomepage, productClickFromPromotedProductsOnHomepage } from "../Functions/HomepagePage"
import { addProductToCartFromProductDetail } from "../Functions/ProductDetailPage"

describe('Tests for adding products to cart', () => {
    beforeEach(() => {
		cy.visit('/')
	})
    it('Product detail - Adding product to cart from product detail and check product in cart', () => {
        productClickFromPromotedProductsOnHomepage(product1_catnum,product1_name)
        cy.url().should('contain', product1_url_prefix_suffix)
        addProductToCartFromProductDetail()
        checkProductAndGoToCartFromFloatingWindow(product1_name_prefix_suffix)
        checkProductInCart(product1_catnum,product1_name_prefix_suffix)
        checkTotalPriceInCart(cart_total_price1)
        cy.url().should('contain', url_kosik)
    })

    it('Homepage promoted products - Adding product to cart from promoted products on homepage and check product in cart', () => {
       addProductToCartFromPromotedProductsOnHomepage(product1_catnum)
       checkProductAndGoToCartFromFloatingWindow(product1_name_prefix_suffix)
       checkProductInCart(product1_catnum,product1_name_prefix_suffix)
       checkTotalPriceInCart(cart_total_price1)
       cy.url().should('contain', url_kosik)
    });

})