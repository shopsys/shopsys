export const checkSuccessfulFlashMessage = (message: string) => {
    cy.getByDataTestId('toast-success').contains(message);
};
