export function checkSuccessfulFlashMessage(message) {
    cy.get('[data-testid="toast-success"]').contains(message);
}
