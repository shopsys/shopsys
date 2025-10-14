import { generateCustomerRegistrationData } from 'fixtures/generators';
import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';

describe('GraphQL error handling', () => {
    it('surfaces schema errors (unknown field)', () => {
        cy.request({
            method: 'POST',
            url: 'graphql/',
            headers: { 'Content-Type': 'application/json' },
            body: { query: 'query { nonExistingField }' },
            failOnStatusCode: false,
        }).then((response) => {
            const body = typeof response.body === 'string' ? JSON.parse(response.body) : response.body;
            expect(body.errors?.[0]?.message || '').to.not.equal('');
        });

        Cypress.once('fail', (err) => {
            expect(String(err.message)).to.contain('InvalidQuery failed');
            return false;
        });

        cy.request({
            method: 'POST',
            url: 'graphql/',
            headers: { 'Content-Type': 'application/json' },
            body: { query: 'query { nonExistingField }' },
            failOnStatusCode: false,
        }).checkGQL('InvalidQuery');
    });

    it('flattens validation errors with input. prefix removed', () => {
        initializePersistStoreInLocalStorageToDefaultValues();
        const validInput = generateCustomerRegistrationData('commonCustomer');
        const invalidInput = { ...validInput, email: 'not-an-email' };
        const body = {
            operationName: 'RegistrationMutation',
            query: `mutation RegistrationMutation($input: RegistrationDataInput!) {
                Register(input: $input) { tokens { accessToken } }
            }`,
            variables: {
                input: invalidInput,
            },
        };

        Cypress.once('fail', (err) => {
            const msg = String(err.message);
            expect(msg).to.contain('RegistrationMutation failed');
            expect(msg).to.match(/Validation:/);
            expect(msg).not.to.contain('input.');
            return false;
        });

        cy.request({
            method: 'POST',
            url: 'graphql/',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
            failOnStatusCode: false,
        }).checkGQL('RegistrationMutation');
    });
});
