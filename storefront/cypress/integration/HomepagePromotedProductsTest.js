describe('Homepage promoted products', () => {
    it('Visits homepage, clicks on Hello Kitty, add product to the cart and checks if redirected correctly', () => {
        cy.visit('/')
        cy.contains('22" Sencor SLE 22F46DM4 HELLO KITTY').click()
        cy.url().should('contain', '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova')
        cy.contains('Do košíku').click()
        cy.contains('Do košíku bylo vloženo zboží 22" Sencor SLE 22F46DM4 HELLO KITTY (1 ks)')    
    })
})