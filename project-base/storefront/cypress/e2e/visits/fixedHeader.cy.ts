import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';
import { TIDs } from 'tids';

const FOCUSABLE_ELEMENTS_SELECTOR =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
const STICKY_NAVIGATION_OFFSET_PROPERTY = '--sticky-navigation-offset';

const showFixedHeader = () => {
    cy.getByTID([TIDs.header])
        .parents('header')
        .then(($header) => {
            const ownerWindow = $header[0].ownerDocument.defaultView;
            const headerBottom = $header[0].getBoundingClientRect().bottom + (ownerWindow?.scrollY ?? 0);

            cy.scrollTo(0, headerBottom + 1);
        });

    cy.getByTID([TIDs.fixed_header]).should('be.visible');
};

describe('Fixed Header Tests', { retries: { runMode: 0 } }, () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('[Sticky Offset] should match the rendered fixed header height on its first display', () => {
        showFixedHeader();

        cy.getByTID([TIDs.fixed_header]).should(($fixedHeader) => {
            const fixedHeaderHeight = $fixedHeader[0].getBoundingClientRect().height;
            const stickyNavigationOffset = $fixedHeader[0].ownerDocument.documentElement.style.getPropertyValue(
                STICKY_NAVIGATION_OFFSET_PROPERTY,
            );

            expect(fixedHeaderHeight).to.be.greaterThan(0);
            expect(Number.parseFloat(stickyNavigationOffset)).to.be.closeTo(fixedHeaderHeight, 1);
        });
    });

    it('[Keyboard Navigation] should continue to page content without scrolling back to the original header', () => {
        showFixedHeader();

        cy.getByTID([TIDs.header])
            .parents('header')
            .should('have.attr', 'aria-hidden', 'true')
            .and('have.attr', 'inert');

        cy.getByTID([TIDs.fixed_header])
            .find(FOCUSABLE_ELEMENTS_SELECTOR)
            .filter(':visible')
            .last()
            .focus();

        cy.window().then((ownerWindow) => {
            const scrollYBeforeTab = ownerWindow.scrollY;

            cy.realPress('Tab');

            cy.focused().should(($focusedElement) => {
                expect($focusedElement.closest('main')).to.have.length(1);
            });
            cy.window().its('scrollY').should('be.closeTo', scrollYBeforeTab, 1);
        });
    });
});
