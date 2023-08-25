export const saveCookiesOptionsInCookiesBar = () => {
    cy.getByDataTestId(['blocks-userconsent', 'blocks-userconsent-save']).click();
    cy.getByDataTestId('blocks-userconsent').should('not.exist');
};
