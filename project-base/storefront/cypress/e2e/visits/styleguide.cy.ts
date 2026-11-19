import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';
import { TIDs } from 'tids';

const SIZES = ['small', 'medium', 'large', 'xlarge'] as const;
const WRAPPERS = [
    TIDs.styleguide_button_,
    TIDs.styleguide_link_button_,
    TIDs.styleguide_submit_button_,
    TIDs.styleguide_icon_button_,
];
const FOCUS_BACKGROUND_COLOR = 'rgb(252, 189, 70)';

const checkDimensions = (element: HTMLElement, height: number, fontSize: number) => {
    expect(element.getBoundingClientRect().height).to.equal(height);
    expect(element.ownerDocument.defaultView?.getComputedStyle(element).fontSize).to.equal(`${fontSize}px`);
};

const assertWrapperDimensions = (size: (typeof SIZES)[number], height: number, fontSize: number) => {
    WRAPPERS.forEach((wrapper) => {
        cy.getByTID([[wrapper, size]]).should(($button) => checkDimensions($button[0], height, fontSize));
    });
};

const assertHoverUnderlinesLink = (tid: TIDs, { shouldShiftColor }: { shouldShiftColor: boolean }) => {
    const checkHoveredLink = ($link: JQuery<HTMLElement>) => {
        const defaultColor = $link.css('color');

        cy.getByTID([tid])
            .realHover()
            .should('have.css', 'text-decoration-line', 'underline')
            .and(shouldShiftColor ? 'not.have.css' : 'have.css', 'color', defaultColor);
    };

    cy.getByTID([tid]).then(checkHoveredLink);
};

const assertWrappingLabel = ($button: JQuery<HTMLElement>, minimumHeight: number) => {
    expect($button[0].getBoundingClientRect().height).to.be.greaterThan(minimumHeight);
    expect($button[0].scrollHeight).to.be.lte($button[0].clientHeight);
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

    it('[Button sizes] should use the shared desktop size scale', () => {
        const heights = [36, 40, 48, 56];
        const fonts = [14, 14, 16, 18];

        SIZES.forEach((size, index) => assertWrapperDimensions(size, heights[index], fonts[index]));
        cy.getByTID([TIDs.styleguide_cms_button]).should(($button) => checkDimensions($button[0], 48, 16));
        cy.getByTID([TIDs.styleguide_input_paired_button]).should(($button) => checkDimensions($button[0], 48, 16));
        cy.getByTID([TIDs.styleguide_wrapping_button]).should(($button) => assertWrappingLabel($button, heights[3]));
    });

    it('[Link hover] should keep content underlines and add navigation and card title underlines on hover', () => {
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
});
