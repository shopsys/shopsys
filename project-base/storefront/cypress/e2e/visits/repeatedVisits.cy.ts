import { DEFAULT_APP_STORE, url } from 'fixtures/demodata';

describe('Repeated page visits tests (for defer testing)', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('5 homepage visits with wait', () => {
        cy.visitAndWaitForStableDOM('/');
        cy.wait(1000);
        cy.visitAndWaitForStableDOM('/');
        cy.wait(1000);
        cy.visitAndWaitForStableDOM('/');
        cy.wait(1000);
        cy.visitAndWaitForStableDOM('/');
        cy.wait(1000);
        cy.visitAndWaitForStableDOM('/');
        cy.wait(1000);
    });

    it('5 homepage visits without wait', () => {
        cy.visitAndWaitForStableDOM('/');
        cy.visitAndWaitForStableDOM('/');
        cy.visitAndWaitForStableDOM('/');
        cy.visitAndWaitForStableDOM('/');
        cy.visitAndWaitForStableDOM('/');
    });

    it('5 product detail page visits with wait', () => {
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.wait(1000);
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.wait(1000);
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.wait(1000);
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.wait(1000);
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.wait(1000);
    });

    it('5 product detail page visits without wait', () => {
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
        cy.visitAndWaitForStableDOM(url.productHelloKitty);
    });

    it('5 category detail page visits with wait', () => {
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.wait(1000);
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.wait(1000);
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.wait(1000);
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.wait(1000);
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.wait(1000);
    });

    it('5 category detail page visits without wait', () => {
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
        cy.visitAndWaitForStableDOM(url.categoryElectronics);
    });
});
