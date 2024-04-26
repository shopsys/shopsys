/// <reference types="cypress-wait-for-stable-dom" />
import './api';
import 'cypress-real-events';
import 'cypress-set-device-pixel-ratio';
import compareSnapshotCommand from 'cypress-visual-regression/dist/command';
import { registerCommand } from 'cypress-wait-for-stable-dom';
import { DEFAULT_APP_STORE } from 'fixtures/demodata';
import { TIDs } from 'tids';

registerCommand({ pollInterval: 500, timeout: 5000 });

Cypress.Commands.add('getByTID', (selectors: ([TIDs, number | string] | TIDs)[]) => {
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

Cypress.Commands.add('visitAndWaitForStableDOM', (url: string) => {
    cy.visit(url);

    return cy.waitForStableDOM();
});

Cypress.Commands.add('reloadAndWaitForStableDOM', () => {
    cy.reload();

    return cy.waitForStableDOM();
});

compareSnapshotCommand({
    capture: 'fullPage',
});

export const checkAndHideSuccessToast = (text?: string) => {
    if (text) {
        cy.getByTID([TIDs.toast_success]).should('contain', text).click().should('not.exist');
    } else {
        cy.getByTID([TIDs.toast_success]).should('exist').click().should('not.exist');
    }
};

export const checkAndHideErrorToast = (text?: string) => {
    if (text) {
        cy.getByTID([TIDs.toast_error]).should('contain', text).click().should('not.exist');
    } else {
        cy.getByTID([TIDs.toast_error]).should('exist').click().should('not.exist');
    }
};

export const checkAndHideInfoToast = (text?: string) => {
    if (text) {
        cy.getByTID([TIDs.toast_info]).should('contain', text).click().should('not.exist');
    } else {
        cy.getByTID([TIDs.toast_info]).should('exist').click().should('not.exist');
    }
};

export const checkUrl = (url: string) => {
    cy.url().should('contain', url);
};

export const goToEditProfileFromHeader = () => {
    cy.getByTID([TIDs.my_account_link])
        .should('be.visible')
        .realHover()
        .then(() => cy.getByTID([TIDs.header_edit_profile_link]).should('be.visible').click());
};

export const checkLoaderOverlayIsNotVisible = (timeout?: number) => {
    cy.getByTID([TIDs.loader_overlay]).should('be.visible', { timeout });
};

export const clickOnLabel = (parentElementId: string) => {
    cy.get(`[for="${parentElementId}"]`).click();
};

export const takeSnapshotAndCompare = (snapshotName: string, capture: 'viewport' | 'fullPage' = 'fullPage') => {
    cy.wait(200);
    cy.setDevicePixelRatio(1);
    cy.screenshot({ capture });
    cy.compareSnapshot(snapshotName, { capture });
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
