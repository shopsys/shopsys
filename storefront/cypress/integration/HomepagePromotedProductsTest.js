import { productClickFromPromoProductsOnHomepage } from "./HomepageFunctions"
import { addProductToCartFromProductDetail } from "./ProductDetailFunctions"

describe('Homepage promoted products', () => {
    it('Visits homepage, clicks on Hello Kitty, add product to the cart and checks if redirected correctly', () => {
        cy.visit('/')
        productClickFromPromoProductsOnHomepage('9177759','22" Sencor SLE 22F46DM4 HELLO KITTY')
        cy.url().should('contain', '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova')
        addProductToCartFromProductDetail()
        cy.contains('Do košíku bylo vloženo zboží 22" Sencor SLE 22F46DM4 HELLO KITTY (1 ks)')    
    })
})