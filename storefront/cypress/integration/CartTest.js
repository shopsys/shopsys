import { product1_catnum, product1_name, product1_name_prefix_suffix, product1_url_prefix_suffix } from "../fixtures/demodata"
import { checkProductInCart } from "./CartPageFunctions"
import { checkProductAndGoToCartFromFloatingWindow } from "./FloatingWindowPageFunctions"
import { productClickFromPromoProductsOnHomepage } from "./HomepageFunctions"
import { addProductToCartFromProductDetail } from "./ProductDetailPageFunctions"

describe('Tests for adding prodcts to cart', () => {
    it.only('Promo products - Adding product to cart from promo products on homepage and check product in cart', () => {
        cy.visit('/')
        productClickFromPromoProductsOnHomepage(product1_catnum,product1_name)
        cy.url().should('contain', product1_url_prefix_suffix)
        addProductToCartFromProductDetail()
        checkProductAndGoToCartFromFloatingWindow(product1_name_prefix_suffix)
        checkProductInCart(product1_catnum,product1_name_prefix_suffix)
    })

    it('Product detail - Adding product to cart from product detail and check product in cart', () => {
       
    });
})