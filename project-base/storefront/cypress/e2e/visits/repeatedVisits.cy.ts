import { DEFAULT_APP_STORE, url } from 'fixtures/demodata';

describe('Repeated page visits tests (for defer testing)', () => {
    beforeEach(() => {
        cy.window().then((win) => {
            win.localStorage.setItem('app-store', JSON.stringify(DEFAULT_APP_STORE));
        });
    });

    it('5 homepage visits with wait', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.wait(1000);
    });

    it('5 homepage visits without wait', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('5 product detail page visits with wait', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.wait(1000);
    });

    it('5 product detail page visits without wait', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
        cy.visitAndWaitForStableAndInteractiveDOM(url.productHelloKitty);
    });

    it('5 category detail page visits with wait', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.wait(1000);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.wait(1000);
    });

    it('5 category detail page visits without wait', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
        cy.visitAndWaitForStableAndInteractiveDOM(url.categoryElectronics);
    });
});
