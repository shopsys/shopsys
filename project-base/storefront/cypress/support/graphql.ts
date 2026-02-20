const parseGraphqlResponse = (response: Cypress.Response<any>): any => {
    return typeof response.body === 'string' ? JSON.parse(response.body) : response.body;
};

export const executeGraphqlQuery = (query: string, variables?: Record<string, any>): Cypress.Chainable<any> => {
    return cy
        .request({
            method: 'POST',
            url: 'graphql/',
            headers: { 'Content-Type': 'application/json' },
            body: { query, variables },
            failOnStatusCode: false,
        })
        .then((response) => parseGraphqlResponse(response));
};
