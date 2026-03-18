/// <reference types="cypress-wait-for-stable-dom" />
import './api';
import { loadAllTranslations, t, type TranslationsType } from './translations';
import 'cypress-real-events';
import { addCompareSnapshotCommand } from 'cypress-visual-regression/dist/command';
import { registerCommand } from 'cypress-wait-for-stable-dom';
import {
    B2B_PERSIST_STORE_NAME,
    b2bDomain,
    DEFAULT_PERSIST_STORE_STATE,
    PERSIST_STORE_NAME,
    staticData,
    url,
} from 'fixtures/demodata';
import { TIDs } from 'tids';

// Global translations object - synchronized access, no race conditions
export let translations: TranslationsType = {} as TranslationsType;

// Auto-load before ALL tests (runs once per test run)
before(() => {
    loadAllTranslations().then((t) => {
        translations = t;
    });
});

// Export for explicit usage if needed
export { loadAllTranslations, t };

registerCommand({ pollInterval: 500, timeout: 5000 });

export enum SNAPSHOT_GROUP {
    MATRIX = 0,
    AUTHENTICATION = 1,
    CART = 2,
    ORDER = 3,
    TRANSPORT_AND_PAYMENT = 4,
    VISITS = 5,
    CUSTOMER_USERS = 6,
    COMPLAINTS = 7,
    LIMITED_USER = 8,
    B2B = 9,
    COMPARISON = 11,
    FILTER = 12,
    STORES = 13,
    SEO_CATEGORY = 14,
    FREE_SHIPPING = 15,
    GIFT = 16,
}

const ELEMENTS_WITH_DISABLED_HOVER_DURING_SCREENSHOTS = [
    '[for="newsletter-form-privacyPolicy"]',
    TIDs.simple_header_contact,
    TIDs.header_cart,
];
const SKIP_SNAPHOTS = Cypress.env('skipSnapshots');

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
                selectorString += `[data-tid=${selectorPrefix}${index}] `;
            } else {
                selectorString += `[data-tid=${selector}] `;
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
    cy.get('.custom-loading-skeleton').should('not.exist');
    cy.get('#nprogress').should('not.exist');
    cy.getByTID([TIDs.loader]).should('not.exist');
    cy.waitForHydration();

    return cy.waitForStableDOM();
});

Cypress.Commands.add('visitAndWaitForStableAndInteractiveDOM', (url: string) => {
    cy.visit(url);
    cy.waitForStableAndInteractiveDOM();

    return cy.waitForStableAndInteractiveDOM();
});

Cypress.Commands.add('reloadAndWaitForStableAndInteractiveDOM', () => {
    cy.reload();
    cy.waitForStableAndInteractiveDOM();

    return cy.waitForStableAndInteractiveDOM();
});

Cypress.Commands.add(
    'visitB2bAndWaitForStableAndInteractiveDOM',
    (path: string, options?: Partial<Cypress.VisitOptions>) => {
        cy.visit(b2bDomain?.baseUrl + path, {
            ...options,
            onBeforeLoad: (win) => {
                win.localStorage.setItem(B2B_PERSIST_STORE_NAME, JSON.stringify(DEFAULT_PERSIST_STORE_STATE));
            },
        });
        cy.waitForStableAndInteractiveDOM();

        return cy.waitForStableAndInteractiveDOM();
    },
);

Cypress.Commands.add('waitForHydration', () => {
    cy.get('body[data-hydrated="true"]', { timeout: 10000 }).should('exist');
});

Cypress.on('uncaught:exception', (err) => {
    if (
        /hydrat/i.test(err.message) ||
        /Minified React error #421/.test(err.message) ||
        /Minified React error #418/.test(err.message)
    ) {
        cy.log('💦 React hydration error', err.message);
        cy.log('💦 React hydration error', err.stack);
        return false;
    }
    return true;
});

addCompareSnapshotCommand({
    capture: 'fullPage',
    errorThreshold: Cypress.env('visualRegressionErrorThreshold'),
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

export const checkIsUserLoggedIn = () => {
    cy.getByTID([TIDs.my_account_link]).should('be.visible').contains(translations.link.myAccount);
};

export const checkIsUserLoggedOut = () => {
    cy.getByTID([TIDs.my_account_link]).should('be.visible').contains(translations.button.login);
};

export const goToEditProfileFromHeader = () => {
    cy.getByTID([TIDs.my_account_link])
        .should('be.visible')
        .realHover()
        .then(() => cy.getByTID([TIDs.user_menu_edit_profile_link]).should('be.visible').click());
    checkUrl(url.customer.editProfile);
    cy.waitForStableAndInteractiveDOM();
};

export const check403PageIsVisible = () => {
    cy.getByTID([TIDs.error_403_page]).should('exist').and('be.visible');
};

export const loginAsB2bOwner = () => {
    cy.loginB2b(staticData.b2bOwner.email, staticData.b2bOwner.password);
};

export const loginAsB2bUser = () => {
    cy.loginB2b(staticData.b2bUser.email, staticData.b2bUser.password);
};

export const loginAsB2bLimitedUser = () => {
    cy.loginB2b(staticData.b2bLimitedUser.email, staticData.b2bLimitedUser.password);
};

export const loginAsB2bCatalogUser = () => {
    cy.loginB2b(staticData.b2bCatalogUser.email, staticData.b2bCatalogUser.password);
};

export const loginAsB2bAccountant = () => {
    cy.loginB2b(staticData.b2bAccountant.email, staticData.b2bAccountant.password);
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
    preserveFixed: TIDs[];
};

export const takeSnapshotAndCompare = (
    testName: string | undefined,
    snapshotName: string,
    options: Partial<SnapshotAdditionalOptions> = {},
    callbackBeforeBlackout?: () => void | undefined,
) => {
    if (SKIP_SNAPHOTS) {
        return;
    }

    const optionsWithDefaultValues = {
        capture: options.capture ?? 'fullPage',
        wait: options.wait ?? 1000,
        blackout: options.blackout ?? [],
        removePointerEvents: options.removePointerEvents ?? [],
        preserveFixed: options.preserveFixed ?? [],
    };

    if (!testName) {
        throw new Error(`Could not resolve test name. Snapshot name was '${snapshotName}'`);
    }

    cy.document().its('fonts.status').should('equal', 'loaded');
    scrollPageBeforeScreenshot(optionsWithDefaultValues);
    hideScrollbars();
    disableStickyPositioningBeforeScreenshot(optionsWithDefaultValues.capture, optionsWithDefaultValues.preserveFixed);
    callbackBeforeBlackout?.();
    disableAnimationsBeforeScreenshot();
    loseFocus();
    blackoutBeforeScreenshot(optionsWithDefaultValues.blackout);
    removePointerEventsBeforeScreenshot([
        ...ELEMENTS_WITH_DISABLED_HOVER_DURING_SCREENSHOTS,
        ...optionsWithDefaultValues.removePointerEvents,
    ]);

    const snapshotNameFormatted = getSnapshotNameFormatted(testName, snapshotName);

    if (optionsWithDefaultValues.capture === 'fullPage' || optionsWithDefaultValues.capture === 'viewport') {
        cy.compareSnapshot(snapshotNameFormatted, { capture: optionsWithDefaultValues.capture });
    } else {
        cy.getByTID([optionsWithDefaultValues.capture]).compareSnapshot(snapshotNameFormatted);
    }

    restoreStickyPositioningAfterScreenshot();
    removeBlackoutsAfterScreenshot();
    resetPointerEventsAfterScreenshot();
    resetAnimationsAfterScreenshot();
};

const getSnapshotNameFormatted = (testName: string, snapshotName: string) => `${testName} ${snapshotName}`;

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

        style.innerHTML = `::-webkit-scrollbar { display: none; } * { scrollbar-width: none !important; }`;
    });
};

const disableStickyPositioningBeforeScreenshot = (
    capture: 'viewport' | 'fullPage' | TIDs,
    preserveFixed: TIDs[] = [],
) => {
    cy.document().then((doc) => {
        const captureEl =
            capture !== 'viewport' && capture !== 'fullPage' ? doc.querySelector(`[data-tid=${capture}]`) : null;

        const preservedEls = preserveFixed
            .map((tid) => doc.querySelector(`[data-tid=${tid}]`))
            .filter(Boolean) as Element[];

        doc.querySelectorAll('*').forEach((el) => {
            const htmlEl = el as HTMLElement;
            const position = window.getComputedStyle(htmlEl).getPropertyValue('position');

            if (position === 'sticky') {
                htmlEl.setAttribute('data-original-position', position);
                htmlEl.style.setProperty('position', 'static', 'important');
            }

            if (position === 'fixed') {
                // Skip hiding the capture target and its ancestors
                if (captureEl && (htmlEl === captureEl || htmlEl.contains(captureEl))) {
                    return;
                }
                // Skip hiding preserved fixed elements, their ancestors, and their siblings
                if (
                    preservedEls.some(
                        (preserved) =>
                            htmlEl === preserved ||
                            htmlEl.contains(preserved) ||
                            (preserved.parentElement && preserved.parentElement === htmlEl.parentElement),
                    )
                ) {
                    return;
                }
                htmlEl.setAttribute('data-original-position', position);
                htmlEl.style.setProperty('display', 'none', 'important');
            }
        });
    });
};

const restoreStickyPositioningAfterScreenshot = () => {
    cy.document().then((doc) => {
        const modifiedElements = doc.querySelectorAll('[data-original-position]');

        modifiedElements.forEach((el) => {
            const htmlEl = el as HTMLElement;
            const originalPosition = htmlEl.getAttribute('data-original-position');

            if (originalPosition === 'fixed') {
                htmlEl.style.removeProperty('display');
            } else {
                htmlEl.style.removeProperty('position');
            }

            htmlEl.removeAttribute('data-original-position');
        });
    });
};

const blackoutBeforeScreenshot = (blackout: Blackout[]) => {
    for (const blackoutElement of blackout) {
        cy.get('body').then(($body) => {
            const matchedElements = $body.find(`[data-tid=${blackoutElement.tid}]`);

            if (!matchedElements.length) {
                return;
            }

            matchedElements.each((_, element) => {
                const rect = element.getBoundingClientRect();

                const coverDiv = document.createElement('div');
                coverDiv.classList.add('blackout');
                coverDiv.style.position = 'absolute';
                coverDiv.style.width = `${rect.width}px`;
                coverDiv.style.height = `${rect.height}px`;
                coverDiv.style.top = `${rect.top + window.scrollY}px`;
                coverDiv.style.left = `${rect.left + window.scrollX}px`;
                coverDiv.style.backgroundColor = 'black';
                coverDiv.style.zIndex = blackoutElement.zIndex ? blackoutElement.zIndex.toString() : '10000';

                $body.append(coverDiv);
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
            if (Object.values(TIDs).includes(selector as TIDs)) {
                return `[data-tid='${selector}']`;
            }
            return selector;
        });

        const selectorString = selectors.join(', ');

        style.innerHTML = `${selectorString} { pointer-events: none !important; }`;
    });
};

const disableAnimationsBeforeScreenshot = () => {
    cy.document().then((doc) => {
        const style = doc.createElement('style');
        style.setAttribute('id', 'disable-animations');
        style.innerHTML = `
            *, *::before, *::after {
                transition: none !important;
                animation: none !important;
                caret-color: transparent !important;
                -webkit-font-smoothing: antialiased !important;
                -moz-osx-font-smoothing: grayscale !important;
            }
        `;
        doc.head.appendChild(style);
    });
};

const resetAnimationsAfterScreenshot = () => {
    cy.document().then((doc) => {
        const style = doc.getElementById('disable-animations');
        if (style) {
            doc.head.removeChild(style);
        }
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
    cy.get('body').then(($body) => {
        const $focused = $body.find(':focus');
        if ($focused.length) {
            cy.focused().blur();
        }
    });
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

    cy.intercept(`/graphql/${requestName}`, (req) => {
        requestCounter += 1;
        req.continue((res) => {
            // delay the response so the mutation stays "in flight" while rapid actions fire
            res.setDelay(2000);
        });
    });

    actions();

    cy.wait(3000).then(() => {
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

export const getSnapshotIndexingFunction = (snapshotGroupIndex: number, snapshotSubgroupIndex: number) => {
    let snapshotCounter = 0;
    let counterAtTestStart = 0;
    let lastTestTitle = '';
    let lastRetryAttempt = 0;

    return () => {
        const currentTest = Cypress.currentTest?.title ?? '';
        const currentRetry = Cypress.currentRetry;

        if (currentTest !== lastTestTitle) {
            lastTestTitle = currentTest;
            counterAtTestStart = snapshotCounter;
            lastRetryAttempt = 0;
        } else if (currentRetry > lastRetryAttempt) {
            snapshotCounter = counterAtTestStart;
            lastRetryAttempt = currentRetry;
        }

        return `${snapshotGroupIndex}-${snapshotSubgroupIndex}-${snapshotCounter++}`;
    };
};

export const checktHeadlineText = (translationKey: string) => {
    return cy.wrap(null).then(() => {
        return cy
            .get('h1')
            .should('exist')
            .and('be.visible')
            .invoke('text')
            .then((actualText) => {
                const trimmedActual = actualText.trim();
                const trimmedKey = translationKey.trim();

                // If the key is directly contained in the H1 text, pass immediately
                if (trimmedActual.includes(trimmedKey)) {
                    expect(true, `✅ H1 text "${trimmedActual}" contains "${trimmedKey}" (direct match)`).to.be.true;
                    return;
                }

                // Use unified translation with fallback (common.json → .po files)
                return t(translationKey).then((translatedText) => {
                    const matches = trimmedActual.includes(translatedText.trim());
                    expect(matches, `✅ H1 text "${trimmedActual}" should contain "${translatedText}"`).to.be.true;
                });
            });
    });
};

export const checkFormLineError = (errorText?: string) => {
    if (errorText) {
        t(errorText).then((translatedText) => {
            const errorSpan = cy.getByTID([TIDs.form_line_error]).contains(translatedText);
            errorSpan.should('exist').and('be.visible');
            return errorSpan;
        });
    } else {
        const errorSpan = cy.getByTID([TIDs.form_line_error]).first();
        errorSpan.should('exist').and('be.visible');
        return errorSpan;
    }
};
