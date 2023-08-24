export function saveCookiesOptionsInCookiesBar() {
    cy.get('[data-testid="blocks-userconsent"] [data-testid="blocks-userconsent-save"]').click();
    cy.get('[data-testid="blocks-userconsent"]').should('not.exist');
}
