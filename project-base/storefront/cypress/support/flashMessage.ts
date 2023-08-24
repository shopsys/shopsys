export const checkSuccessfulFlashMessage = (message: string) => {
    cy.get('[data-testid="toast-success"]').contains(message);
}
