export function continueToSecondStep() {
    cy.get('[data-testid="blocks-orderaction-next"]').contains(button_cart_continue_to_2step).click();
}
