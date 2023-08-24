export function checkSuccessfulFlashMessage(message: string) {
    cy.get('[data-testid="toast-success"]').contains(message);
}
