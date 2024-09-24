/// <reference types="cypress-wait-for-stable-dom" />
import './api';
import 'cypress-real-events';
import compareSnapshotCommand from 'cypress-visual-regression/dist/command';
import { registerCommand } from 'cypress-wait-for-stable-dom';
import { DEFAULT_PERSIST_STORE_STATE, PERSIST_STORE_NAME, url } from 'fixtures/demodata';
import { TIDs } from 'tids';

registerCommand({ pollInterval: 500, timeout: 5000 });

const FILENAME_LENGTH_LIMIT = 250;
const ELEMENTS_WITH_DISABLED_HOVER_DURING_SCREENSHOTS = [
    '[for="newsletter-form-privacyPolicy"]',
    TIDs.simple_header_contact,
];

Cypress.Commands.add(
    'getByTID',
    (
        selectors: ([TIDs, number | string] | TIDs)[],
        options?: Partial<Cypress.Loggable & Cypress.Timeoutable & Cypress.Withinable & Cypress.Shadow> | undefined,
    ) => {
        let selectorString = '';
        for (const selector of selectors) {
            if (Array.isArray(selector)) {
                const [selectorPrefix, index] = selector;
                selectorString += `[tid=${selectorPrefix}${index}] `;
            } else {
                selectorString += `[tid=${selector}] `;
            }
        }

        return cy.get(selectorString.trim(), options);
    },
);

Cypress.Commands.add('storeCartUuidInLocalStorage', (cartUuid: string) => {
    return cy.then(() => {
        const currentAppStoreAsString = window.localStorage.getItem(PERSIST_STORE_NAME);
        let currentAppStore = DEFAULT_PERSIST_STORE_STATE;
        if (currentAppStoreAsString) {
            currentAppStore = JSON.parse(currentAppStoreAsString);
        }
        currentAppStore.state.cartUuid = cartUuid;

        window.localStorage.setItem(PERSIST_STORE_NAME, JSON.stringify(currentAppStore));
    });
});

Cypress.Commands.add('waitForStableAndInteractiveDOM', () => {
    cy.waitForStableDOM();
    cy.window().then((win) => {
        win.dispatchEvent(new Event('resize'));
    });
    cy.get('.react-loading-skeleton').should('not.exist');
    cy.get('#nprogress').should('not.exist');
    cy.getByTID([TIDs.loader]).should('not.exist');

    return cy.waitForStableDOM();
});

Cypress.Commands.add('visitAndWaitForStableAndInteractiveDOM', (url: string) => {
    cy.visit(url);

    return cy.waitForStableAndInteractiveDOM();
});

Cypress.Commands.add('reloadAndWaitForStableAndInteractiveDOM', () => {
    cy.reload();

    return cy.waitForStableAndInteractiveDOM();
});

compareSnapshotCommand({
    capture: 'fullPage',
    errorThreshold: 0.005,
});

export const initializePersistStoreInLocalStorageToDefaultValues = () => {
    cy.window().then((win) => {
        win.localStorage.setItem(PERSIST_STORE_NAME, JSON.stringify(DEFAULT_PERSIST_STORE_STATE));
    });
};

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
    checkUrl(url.customer.editProfile);
    cy.waitForStableAndInteractiveDOM();
};

export const checkLoaderOverlayIsNotVisibleAfterTimePeriod = (timePeriod: number = 300) => {
    cy.wait(timePeriod);
    cy.getByTID([TIDs.loader_overlay]).should('not.exist');
};

export const clickOnLabel = (parentElementId: string) => {
    cy.get(`[for="${parentElementId}"]`).click();
};

export type Blackout = { tid: TIDs; zIndex?: number };

type SnapshotAdditionalOptions = {
    capture: 'viewport' | 'fullPage' | TIDs;
    wait: number;
    blackout: Blackout[];
    removePointerEvents: (TIDs | string)[];
};

export const takeSnapshotAndCompare = (
    testName: string | undefined,
    snapshotName: string,
    options: Partial<SnapshotAdditionalOptions> = {},
    callbackBeforeBlackout?: () => void | undefined,
) => {
    const optionsWithDefaultValues = {
        capture: options.capture ?? 'fullPage',
        wait: options.wait ?? 1000,
        blackout: options.blackout ?? [],
        removePointerEvents: options.removePointerEvents ?? [],
    };

    if (!testName) {
        throw new Error(`Could not resolve test name. Snapshot name was '${snapshotName}'`);
    }

    scrollPageBeforeScreenshot(optionsWithDefaultValues);
    hideScrollbars();
    callbackBeforeBlackout?.();
    blackoutBeforeScreenshot(optionsWithDefaultValues.blackout);
    removePointerEventsBeforeScreenshot(ELEMENTS_WITH_DISABLED_HOVER_DURING_SCREENSHOTS);

    const snapshotNameFormatted = getSnapshotNameFormatted(testName, snapshotName);

    if (optionsWithDefaultValues.capture === 'fullPage' || optionsWithDefaultValues.capture === 'viewport') {
        cy.compareSnapshot(snapshotNameFormatted, { capture: optionsWithDefaultValues.capture });
    } else {
        cy.getByTID([optionsWithDefaultValues.capture]).compareSnapshot(snapshotNameFormatted);
    }

    removeBlackoutsAfterScreenshot();
    resetPointerEventsAfterScreenshot();
};

const getSnapshotNameFormatted = (testName: string, snapshotName: string) => {
    // get the test name summary in square brackets using regex
    const testNameSummary = testName.match(/\[(.*?)\]/)?.[0] ?? '';
    const testNameRest = testNameSummary ? testName.replace(testNameSummary + ' ', '') : testName;
    const filenameLengthSum =
        (testNameSummary ? testNameSummary.length + 1 : 0) + snapshotName.length + testNameRest.length + 3;

    return getStringWithAllInfo(
        testNameSummary,
        snapshotName,
        filenameLengthSum < FILENAME_LENGTH_LIMIT
            ? testNameRest
            : `${testNameRest?.slice(FILENAME_LENGTH_LIMIT - filenameLengthSum)}`,
    );
};

const getStringWithAllInfo = (summary: string, snapshotName: string, testName: string) => {
    return `${summary ? summary + ' ' : ''}(${snapshotName}) ${testName}`;
};

const scrollPageBeforeScreenshot = (optionsWithDefaultValues: SnapshotAdditionalOptions) => {
    if (optionsWithDefaultValues.capture === 'fullPage' || optionsWithDefaultValues.capture === 'viewport') {
        cy.wait(optionsWithDefaultValues.wait / 5);
        cy.scrollTo('bottomLeft', { duration: optionsWithDefaultValues.wait / 5 });
        cy.wait(optionsWithDefaultValues.wait / 5);
        cy.scrollTo('topLeft', { duration: optionsWithDefaultValues.wait / 5 });
        cy.wait(optionsWithDefaultValues.wait / 5);
    } else {
        cy.wait(optionsWithDefaultValues.wait);
    }
};

const hideScrollbars = () => {
    cy.document().then((doc) => {
        const style = doc.createElement('style');
        style.setAttribute('id', 'hide-scrollbars');
        doc.head.appendChild(style);

        style.innerHTML = `::-webkit-scrollbar { display: none; }`;
    });
};

const blackoutBeforeScreenshot = (blackout: Blackout[]) => {
    for (const blackoutElement of blackout) {
        cy.getByTID([blackoutElement.tid]).each((element) => {
            const rect = element[0].getBoundingClientRect();

            const coverDiv = document.createElement('div');
            coverDiv.classList.add('blackout');
            coverDiv.style.position = 'absolute';
            coverDiv.style.width = `${rect.width}px`;
            coverDiv.style.height = `${rect.height}px`;
            coverDiv.style.top = `${rect.top + window.scrollY}px`;
            coverDiv.style.left = `${rect.left + window.scrollX}px`;
            coverDiv.style.backgroundColor = 'black';
            coverDiv.style.zIndex = blackoutElement.zIndex ? blackoutElement.zIndex.toString() : '10000';

            cy.get('body').then((body) => {
                body.append(coverDiv);
            });
        });
    }
};

const removeBlackoutsAfterScreenshot = () => {
    cy.get('body').then(($body) => {
        if ($body.find('.blackout').length) {
            $body.find('.blackout').each(function () {
                this.remove();
            });
        }
    });
};

const removePointerEventsBeforeScreenshot = (removePointerEvents: (TIDs | string)[]) => {
    cy.document().then((doc) => {
        const style = doc.createElement('style');
        style.setAttribute('id', 'disable-pointer-events');
        doc.head.appendChild(style);

        const selectors = removePointerEvents.map((selector) => {
            if (Object.values<any>(TIDs).includes(selector)) {
                return `[tid='${selector}']`;
            }
            return selector;
        });

        const selectorString = selectors.join(', ');

        style.innerHTML = `${selectorString} { pointer-events: none !important; }`;
    });
};

const resetPointerEventsAfterScreenshot = () => {
    cy.document().then((doc) => {
        const style = doc.getElementById('disable-pointer-events');
        if (style) {
            doc.head.removeChild(style);
        }
    });
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

export const checkPopupIsVisible = (shouldCloseAfterChecking: boolean = false) => {
    cy.getByTID([TIDs.layout_popup]).should('be.visible');

    if (shouldCloseAfterChecking) {
        cy.realPress('{esc}');
    }
};

export const checkNumberOfApiRequestsTriggeredByActions = (
    actions: () => void,
    numberOfRequests: number,
    requestName: string,
) => {
    let requestCounter = 0;

    cy.intercept(`/graphql/${requestName}`, () => {
        requestCounter += 1;
    });

    actions();

    cy.wait(1000).then(() => {
        expect(requestCounter).to.eq(numberOfRequests);
    });
};

export const changeCartItemQuantityWithSpinboxInput = (quantity: number, catnum: string) => {
    cy.getByTID([[TIDs.pages_cart_list_item_, catnum], TIDs.spinbox_input]).type(quantity.toString());
};

export const changeProductListItemQuantityWithSpinboxInput = (quantity: number, catnum: string) => {
    cy.getByTID([[TIDs.blocks_product_list_listeditem_, catnum], TIDs.spinbox_input]).type(quantity.toString());
};

export const goToPageThroughSimpleNavigation = (index: number) => {
    cy.getByTID([[TIDs.blocks_simplenavigation_, index]]).click();
    cy.waitForStableAndInteractiveDOM();
};

export const checkCanGoToNextOrderStep = () => {
    cy.getByTID([TIDs.blocks_orderaction_next]).should('be.visible').and('not.be.disabled');
};
