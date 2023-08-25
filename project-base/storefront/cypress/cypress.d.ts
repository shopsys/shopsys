declare global {
    namespace Cypress {
        interface Chainable<Subject = any> {
            getByDataTestId(value: string | string[]): Chainable<JQuery<HTMLElement>>;
        }
    }
}

export {};
