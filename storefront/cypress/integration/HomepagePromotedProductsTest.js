import { checkProductInCart } from "./CartPageFunctions"
import { checkProductAndGoToCartFromFloatingWindow } from "./FloatingWindowPageFunctions"
import { productClickFromPromoProductsOnHomepage } from "./HomepageFunctions"
import { addProductToCartFromProductDetail } from "./ProductDetailPageFunctions"

describe('Homepage promoted products', () => {
    it('Visits homepage, clicks on Hello Kitty, add product to the cart and checks if redirected correctly', () => {
        cy.visit('/')
        productClickFromPromoProductsOnHomepage('9177759','22" Sencor SLE 22F46DM4 HELLO KITTY')
        cy.url().should('contain', '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova')
        addProductToCartFromProductDetail()
        checkProductAndGoToCartFromFloatingWindow('Televize 22" Sencor SLE 22F46DM4 HELLO KITTY plazmová')
        checkProductInCart('9177759', 'Televize 22" Sencor SLE 22F46DM4 HELLO KITTY plazmová')
    })
})