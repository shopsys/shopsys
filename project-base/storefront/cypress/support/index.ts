/// <reference types="cypress-wait-for-stable-dom" />
import './api';
import 'cypress-real-events';
import 'cypress-set-device-pixel-ratio';
import compareSnapshotCommand from 'cypress-visual-regression/dist/command';
import { registerCommand } from 'cypress-wait-for-stable-dom';
import { DEFAULT_APP_STORE } from 'fixtures/demodata';
import { TIDs } from 'tids';

registerCommand();

Cypress.Commands.add('getByTID', (selectors: ([TIDs, number] | TIDs)[]) => {
    let selectorString = '';
    for (const selector of selectors) {
        if (Array.isArray(selector)) {
            const [selectorPrefix, index] = selector;
            selectorString += `[tid=${selectorPrefix}${index}] `;
        } else {
            selectorString += `[tid=${selector}] `;
        }
    }

    return cy.get(selectorString.trim());
});

Cypress.Commands.add('storeCartUuidInLocalStorage', (cartUuid: string) => {
    return cy.then(() => {
        const currentAppStoreAsString = window.localStorage.getItem('app-store');
        let currentAppStore = DEFAULT_APP_STORE;
        if (currentAppStoreAsString) {
            currentAppStore = JSON.parse(currentAppStoreAsString);
        }
        currentAppStore.state.cartUuid = cartUuid;

        window.localStorage.setItem('app-store', JSON.stringify(currentAppStore));
    });
});

compareSnapshotCommand({
    capture: 'fullPage',
});

export const checkAndHideSuccessToast = () => {
    cy.getByTID([TIDs.toast_success]).should('exist').click().should('not.exist');
};

export const checkUrl = (url: string) => {
    cy.url().should('contain', url);
};

export const checkLoaderOverlayIsNotVisible = (timeout?: number) => {
    cy.getByTID([TIDs.loader_overlay]).should('be.visible', { timeout });
};

export const takeSnapshotAndCompare = (snapshotName: string) => {
    cy.wait(200);
    cy.setDevicePixelRatio(1);
    cy.screenshot();
    cy.compareSnapshot(snapshotName);
};

export const changeElementText = (selector: TIDs, newText: string, isRightAfterSSR = true) => {
    if (isRightAfterSSR) {
        cy.wait(200);
    }
    cy.getByTID([selector]).then((element) => {
        element.text(newText);
    });
};

export const loseFocus = () => {
    cy.focused().blur();
};
