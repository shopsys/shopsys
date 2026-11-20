import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';
import { TIDs } from 'tids';

const ICON_BUTTON_SIZES = ['compact', 'small', 'medium', 'large'] as const;
const TEXT_BUTTON_WRAPPERS = [
    TIDs.styleguide_button_,
    TIDs.styleguide_link_button_,
    TIDs.styleguide_submit_button_,
    TIDs.styleguide_button_with_icon_,
];
const FOCUS_BACKGROUND_COLOR = 'rgb(252, 189, 70)';
const VIEWPORT_HEIGHT = 720;

type TextButtonSize = 'small' | 'medium' | 'large' | 'xlarge';
type ButtonDimensions = { height: number; fontSize: number };
type TextButtonScale = { sizes: Record<TextButtonSize, ButtonDimensions>; cmsButton: ButtonDimensions };

// Text buttons switch their scale at the vl breakpoint (1024 px); the pointer type does not change it.
const BELOW_VL_SCALE: TextButtonScale = {
    sizes: {
        small: { height: 36, fontSize: 14 },
        medium: { height: 40, fontSize: 14 },
        large: { height: 44, fontSize: 16 },
        xlarge: { height: 44, fontSize: 16 },
    },
    cmsButton: { height: 44, fontSize: 16 },
};
const FROM_VL_SCALE: TextButtonScale = {
    sizes: {
        small: { height: 36, fontSize: 14 },
        medium: { height: 40, fontSize: 14 },
        large: { height: 48, fontSize: 16 },
        xlarge: { height: 56, fontSize: 18 },
    },
    cmsButton: { height: 48, fontSize: 16 },
};
const VIEWPORT_SCALES = [
    { viewportWidth: 375, scale: BELOW_VL_SCALE },
    { viewportWidth: 1023, scale: BELOW_VL_SCALE },
    { viewportWidth: 1024, scale: FROM_VL_SCALE },
    { viewportWidth: 1600, scale: FROM_VL_SCALE },
];
const COARSE_POINTER_VIEWPORT_SCALES = [VIEWPORT_SCALES[0], VIEWPORT_SCALES[2]];
const ICON_BUTTON_DIMENSIONS: Record<(typeof ICON_BUTTON_SIZES)[number], number> = {
    compact: 24,
    small: 32,
    medium: 40,
    large: 48,
};
const INPUT_PAIRED_DIMENSIONS: ButtonDimensions = { height: 48, fontSize: 16 };
const INPUT_PAIRED_INPUT_HEIGHT = 48;

const setTouchEmulation = (isEnabled: boolean) =>
    Cypress.automation('remote:debugger:protocol', {
        command: 'Emulation.setTouchEmulationEnabled',
        params: { enabled: isEnabled, maxTouchPoints: 1 },
    });

const assertPointerMedia = (query: string) => {
    cy.window().should((win) => expect(win.matchMedia(query).matches, query).to.equal(true));
};

// cypress.config.ts declares a desktop mouse for headless Chromium.
const assertDesktopPointer = () => {
    assertPointerMedia('(pointer: fine)');
    assertPointerMedia('(hover: hover)');
};

const emulateCoarsePointer = () => {
    cy.then(() => setTouchEmulation(true));
    assertPointerMedia('(pointer: coarse)');
};

// Buttons animate size changes (transition-all), so callers assert through retried should() callbacks
// to measure the final rendered box after a viewport change.
const checkDimensions = (element: HTMLElement, { height, fontSize }: ButtonDimensions, context: string) => {
    expect(element.getBoundingClientRect().height, `${context} height`).to.equal(height);
    expect(element.ownerDocument.defaultView?.getComputedStyle(element).fontSize, `${context} font size`).to.equal(
        `${fontSize}px`,
    );
};

const assertWrappingLabel = ($button: JQuery<HTMLElement>, singleLineHeight: number) => {
    expect($button[0].getBoundingClientRect().height).to.be.greaterThan(singleLineHeight);
    expect($button[0].scrollHeight).to.be.lte($button[0].clientHeight);
    expect($button[0].scrollWidth).to.be.lte($button[0].clientWidth);
};

const assertTextButtonSize = (size: TextButtonSize, dimensions: ButtonDimensions, viewportWidth: number) => {
    for (const wrapper of TEXT_BUTTON_WRAPPERS) {
        const context = `${wrapper}${size} at ${viewportWidth}px`;

        cy.getByTID([[wrapper, size]]).should(($button) => checkDimensions($button[0], dimensions, context));
    }
};

const assertInputPairedButton = (viewportWidth: number) => {
    const context = `input paired button at ${viewportWidth}px`;

    cy.getByTID([TIDs.styleguide_input_paired_button]).should(($button) =>
        checkDimensions($button[0], INPUT_PAIRED_DIMENSIONS, context),
    );
    cy.getByTID([TIDs.styleguide_input_pair])
        .find('input')
        .should(($input) =>
            expect($input[0].getBoundingClientRect().height, `paired input at ${viewportWidth}px`).to.equal(
                INPUT_PAIRED_INPUT_HEIGHT,
            ),
        );
};

const assertTextButtonScale = (viewportWidth: number, scale: TextButtonScale) => {
    for (const [size, dimensions] of Object.entries(scale.sizes) as [TextButtonSize, ButtonDimensions][]) {
        assertTextButtonSize(size, dimensions, viewportWidth);
    }

    cy.getByTID([TIDs.styleguide_cms_button]).should(($button) =>
        checkDimensions($button[0], scale.cmsButton, `CMS button at ${viewportWidth}px`),
    );
    assertInputPairedButton(viewportWidth);
    cy.getByTID([TIDs.styleguide_wrapping_button]).should(($button) =>
        assertWrappingLabel($button, scale.sizes.xlarge.height),
    );
};

const checkSquareDimensions = (element: HTMLElement, dimension: number, context: string) => {
    const { height, width } = element.getBoundingClientRect();

    expect(height, `${context} height`).to.equal(dimension);
    expect(width, `${context} width`).to.equal(dimension);
};

const assertIconButtonScale = (viewportWidth: number) => {
    for (const size of ICON_BUTTON_SIZES) {
        const context = `${TIDs.styleguide_icon_button_}${size} at ${viewportWidth}px`;

        cy.getByTID([[TIDs.styleguide_icon_button_, size]]).should(($iconButton) =>
            checkSquareDimensions($iconButton[0], ICON_BUTTON_DIMENSIONS[size], context),
        );
    }
};

const assertButtonScalesAcrossViewports = (viewportScales: typeof VIEWPORT_SCALES) => {
    for (const { viewportWidth, scale } of viewportScales) {
        cy.viewport(viewportWidth, VIEWPORT_HEIGHT);
        assertTextButtonScale(viewportWidth, scale);
        assertIconButtonScale(viewportWidth);
    }
};

const assertHoverUnderlinesLink = (tid: TIDs, { shouldShiftColor }: { shouldShiftColor: boolean }) => {
    const hoverAndCheckLink = ($link: JQuery<HTMLElement>) => {
        const defaultColor = $link.css('color');

        cy.wrap($link).realHover();
        // realHover is not a query, so a fresh query keeps the hover assertions retried.
        cy.getByTID([tid])
            .should('have.css', 'text-decoration-line', 'underline')
            .and(shouldShiftColor ? 'not.have.css' : 'have.css', 'color', defaultColor);
    };

    cy.getByTID([tid]).then(hoverAndCheckLink);
};

const assertContentLinkHover = (tid: TIDs) => {
    cy.getByTID([tid]).should('have.css', 'text-decoration-line', 'underline');
    assertHoverUnderlinesLink(tid, { shouldShiftColor: true });
};

const assertFocusKeepsLinkButtonHeight = ($button: JQuery<HTMLElement>) => {
    const height = $button[0].getBoundingClientRect().height;

    cy.getByTID([[TIDs.styleguide_button_, 'small']]).focus();
    cy.realPress('Tab');
    cy.getByTID([[TIDs.styleguide_link_button_, 'small']])
        .should('be.focused')
        .and(($focusedButton) => expect($focusedButton[0].getBoundingClientRect().height).to.equal(height));
};

describe('Styleguide Tests (SSP-4301)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visit('/styleguide');
        // The loading example is intentionally persistent, so global "no loaders" readiness does not apply.
        cy.waitForHydration();
        cy.getByTID([[TIDs.styleguide_button_, 'small']]).should('be.visible');
    });

    // Disabling touch also resets desktop pointer media, so clean up only after the final touch test.
    after(() => {
        cy.then(() => setTouchEmulation(false));
    });

    it('[Button sizes] should use the text button scale below and from the vl breakpoint and keep the icon button scale', () => {
        assertDesktopPointer();
        assertButtonScalesAcrossViewports(VIEWPORT_SCALES);
    });

    it('[Link hover] should keep content underlines and add navigation and card title underlines on hover', () => {
        assertDesktopPointer();
        [TIDs.styleguide_content_link, TIDs.styleguide_text_action, TIDs.styleguide_inverted_link].forEach(
            assertContentLinkHover,
        );

        cy.getByTID([TIDs.styleguide_navigation_link]).should('have.css', 'text-decoration-line', 'none');
        assertHoverUnderlinesLink(TIDs.styleguide_navigation_link, { shouldShiftColor: false });

        cy.getByTID([TIDs.styleguide_card_title]).should('have.css', 'text-decoration-line', 'none');
        cy.getByTID([TIDs.styleguide_card_link]).realHover();
        cy.getByTID([TIDs.styleguide_card_title]).should('have.css', 'text-decoration-line', 'underline');
        cy.getByTID([TIDs.styleguide_card_text]).should('have.css', 'text-decoration-line', 'none');
    });

    it('[Keyboard focus] should keep the orange focus without changing layout and keep native disabled states', () => {
        cy.getByTID([TIDs.styleguide_content_link]).focus();
        cy.realPress('Tab');
        cy.getByTID([TIDs.styleguide_text_action])
            .should('be.focused')
            .and('have.css', 'background-color', FOCUS_BACKGROUND_COLOR);

        cy.getByTID([[TIDs.styleguide_link_button_, 'small']]).then(assertFocusKeepsLinkButtonHeight);

        cy.getByTID([TIDs.styleguide_disabled_button]).should('be.disabled');
        cy.getByTID([TIDs.styleguide_loading_button]).should('be.disabled');
    });

    // Keep this test last: after touch emulation is disabled, the pointer stays (pointer: none) until a top-level
    // navigation, and cy.visit navigates only the application frame. Cypress opens a new tab for the next spec.
    it('[Coarse pointer] should keep the same text and icon button sizes for touch input', () => {
        emulateCoarsePointer();
        assertButtonScalesAcrossViewports(COARSE_POINTER_VIEWPORT_SCALES);
    });
});
