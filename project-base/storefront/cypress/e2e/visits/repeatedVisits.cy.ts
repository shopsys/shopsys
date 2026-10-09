import { staticData } from 'fixtures/demodata';
import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';
import { visitEntityByUuid } from 'support/navigation';

describe('Repeated Page Visits Tests (Defer Testing)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[Slow Homepage] 5 homepage visits with wait', () => {
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

    it('[Fast Homepage] 5 homepage visits without wait', () => {
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.visitAndWaitForStableAndInteractiveDOM('/');
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('[Slow Product Detail] 5 product detail page visits with wait', () => {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        cy.wait(1000);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        cy.wait(1000);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        cy.wait(1000);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        cy.wait(1000);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        cy.wait(1000);
    });

    it('[Fast Product Detail] 5 product detail page visits without wait', () => {
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
        visitEntityByUuid('product', staticData.products.helloKitty.uuid);
    });

    it('[Slow Category Detail] 5 category detail page visits with wait', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.wait(1000);
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.wait(1000);
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.wait(1000);
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.wait(1000);
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.wait(1000);
    });

    it('[Fast Category Detail] 5 category detail page visits without wait', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
    });
});
