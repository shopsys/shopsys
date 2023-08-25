Cypress.Commands.add('getByDataTestId', (selectors: string | string[]) => {
    if (typeof selectors === 'string') {
        selectors = [selectors];
    }

    let selectorString = '';
    for (let i = 0; i < selectors.length; i++) {
        selectorString += `[data-testid="${selectors[i]}"] `;
    }

    return cy.get(selectorString.trim());
});
