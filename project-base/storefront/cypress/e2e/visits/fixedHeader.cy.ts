import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';
import { TIDs } from 'tids';

const FOCUSABLE_ELEMENTS_SELECTOR =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
const STICKY_NAVIGATION_OFFSET_PROPERTY = '--sticky-navigation-offset';
const ARTICLE_HEADING_ID = 'start-with-screen-size-and-viewing-distance';
const BLOG_ARTICLE_PATH = '/how-to-choose-the-right-tv-for-your-living-room';
const BLOG_CATEGORY_PATH = '/blog';
const PRODUCT_PATH = '/television-22-sencor-sle-22f46dm4-hello-kitty-plasma';
const CATEGORY_PATH = '/tv-audio';

const checkAccountPopoverAriaReferences = (headerTid: TIDs.header | TIDs.fixed_header, loginFormName: string) => {
    const descriptionId = `${loginFormName}-description`;
    const benefitsTitleId = `${loginFormName}-registration-benefits-title`;
    const benefitsListId = `${loginFormName}-registration-benefits-list`;

    cy.get(`#${descriptionId}`).should('have.length', 1);
    cy.getByTID([headerTid, TIDs.my_account_link, TIDs.login_form])
        .find('input[type="email"]')
        .should('have.attr', 'aria-describedby', descriptionId);
    cy.get(`#${benefitsTitleId}`).should('have.length', 1);
    cy.getByTID([headerTid, TIDs.my_account_link])
        .find(`section[aria-labelledby="${benefitsTitleId}"]`)
        .should('exist');
    cy.get(`#${benefitsListId}`).should('have.length', 1);
    cy.getByTID([headerTid, TIDs.my_account_link, TIDs.login_popup_register_button])
        .should('have.attr', 'aria-describedby', benefitsListId);
};

const checkTargetIsBelowFixedHeader = (targetTid: TIDs | [TIDs, string]) => {
    cy.getByTID([TIDs.fixed_header])
        .should('be.visible')
        .then(($fixedHeader) => {
            const fixedHeaderBottom = $fixedHeader[0].getBoundingClientRect().bottom;

            cy.getByTID([targetTid]).should(($target) => {
                expect($target[0].getBoundingClientRect().top).to.be.gte(fixedHeaderBottom);
            });
        });
};

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

    it('[Sticky Offset] should keep the blog sidebar below the fixed header', () => {
        cy.getByTID([TIDs.blog_preview_image]).first().closest('a').click();
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.blog_sidebar]).then(($blogSidebar) => {
            const ownerWindow = $blogSidebar[0].ownerDocument.defaultView;
            const blogSidebarDocumentTop = $blogSidebar[0].getBoundingClientRect().top + (ownerWindow?.scrollY ?? 0);

            cy.scrollTo(0, blogSidebarDocumentTop + 1);
        });

        cy.getByTID([TIDs.fixed_header]).should('be.visible').then(($fixedHeader) => {
            const fixedHeaderBottom = $fixedHeader[0].getBoundingClientRect().bottom;

            cy.getByTID([TIDs.blog_sidebar]).should(($blogSidebar) => {
                expect($blogSidebar[0].getBoundingClientRect().top).to.be.greaterThan(fixedHeaderBottom);
            });
        });
    });

    it('[Keyboard Navigation] should continue to page content without scrolling back to the original header', () => {
        showFixedHeader();

        cy.getByTID([TIDs.header])
            .parents('header')
            .should('have.attr', 'aria-hidden', 'true')
            .and('have.attr', 'inert');

        // Focusing the last navigation trigger opens its submenu, so keyboard navigation leaves from its last link.
        cy.getByTID([TIDs.fixed_header])
            .find(FOCUSABLE_ELEMENTS_SELECTOR)
            .filter(':visible')
            .last()
            .focus()
            .should('have.attr', 'aria-expanded', 'true')
            .invoke('attr', 'aria-controls')
            .should('be.a', 'string')
            .then((navigationMenuId) => {
                cy.getByTID([TIDs.fixed_header])
                    .find(`#${navigationMenuId}`)
                    .find('a[href]')
                    .last()
                    .focus();
            });

        cy.window().then((ownerWindow) => {
            const scrollYBeforeTab = ownerWindow.scrollY;

            cy.realPress('Tab');

            cy.focused().should(($focusedElement) => {
                expect($focusedElement.closest('main')).to.have.length(1);
            });
            cy.window().its('scrollY').should('be.closeTo', scrollYBeforeTab, 1);
        });
    });

    it('[Account Popovers] should keep ARIA references unique across the regular and fixed headers', () => {
        showFixedHeader();

        cy.getByTID([TIDs.header, TIDs.my_account_link]).trigger('mouseover', {
            force: true,
            scrollBehavior: false,
        });
        cy.getByTID([TIDs.header, TIDs.my_account_link, TIDs.login_form]).should('exist');

        cy.getByTID([TIDs.fixed_header, TIDs.my_account_link]).trigger('mouseover', {
            force: true,
            scrollBehavior: false,
        });
        cy.getByTID([TIDs.fixed_header, TIDs.my_account_link, TIDs.login_form]).should('be.visible');

        checkAccountPopoverAriaReferences(TIDs.header, 'header-login-form');
        checkAccountPopoverAriaReferences(TIDs.fixed_header, 'fixed-header-login-form');
    });

    it('[Article Anchor] should place a clicked article heading below the fixed header', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(BLOG_ARTICLE_PATH);

        cy.getByTID([[TIDs.blog_article_anchor_navigation_link_, ARTICLE_HEADING_ID]]).click();

        cy.getByTID([TIDs.fixed_header])
            .should('be.visible')
            .then(($fixedHeader) => {
                const fixedHeaderBottom = $fixedHeader[0].getBoundingClientRect().bottom;

                cy.getByTID([TIDs.blog_article_content])
                    .find(`#${ARTICLE_HEADING_ID}`)
                    .should(($heading) => {
                        expect($heading[0].getBoundingClientRect().top).to.be.gte(fixedHeaderBottom);
                    });
            });
    });

    it('[Article Deep Link] should place a directly linked article heading below the fixed header', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(`${BLOG_ARTICLE_PATH}#${ARTICLE_HEADING_ID}`);

        cy.getByTID([TIDs.fixed_header])
            .should('be.visible')
            .then(($fixedHeader) => {
                const fixedHeaderBottom = $fixedHeader[0].getBoundingClientRect().bottom;

                cy.getByTID([TIDs.blog_article_content])
                    .find(`#${ARTICLE_HEADING_ID}`)
                    .should(($heading) => {
                        expect($heading[0].getBoundingClientRect().top).to.be.gte(fixedHeaderBottom);
                    });
            });
    });

    it('[Blog Pagination] should restore a paginated article list below the fixed header', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(`${BLOG_CATEGORY_PATH}?page=2`);

        checkTargetIsBelowFixedHeader(TIDs.blog_article_list);
    });

    it('[Product Deep Link] should place a directly linked product section below both sticky navigations', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(`${PRODUCT_PATH}#parameters`);

        cy.getByTID([TIDs.fixed_header])
            .should('be.visible')
            .then(($fixedHeader) => {
                const fixedHeaderBottom = $fixedHeader[0].getBoundingClientRect().bottom;

                cy.getByTID([TIDs.product_detail_section_navigation])
                    .should(($sectionNavigation) => {
                        const sectionNavigationRect = $sectionNavigation[0].getBoundingClientRect();

                        expect(sectionNavigationRect.height, 'product section navigation height').to.be.greaterThan(0);
                        expect(sectionNavigationRect.top, 'product section navigation top').to.be.gte(
                            fixedHeaderBottom,
                        );
                    })
                    .then(($sectionNavigation) => {
                        const sectionNavigationBottom = $sectionNavigation[0].getBoundingClientRect().bottom;

                        cy.getByTID([[TIDs.product_detail_section_, 'parameters']]).should(($target) => {
                            expect($target[0].getBoundingClientRect().top, 'parameters section top').to.be.gte(
                                sectionNavigationBottom,
                            );
                        });
                    });
            });
    });

    it('[Product List Anchor] should place a directly linked product list below the fixed header', () => {
        cy.visitAndWaitForStableAndInteractiveDOM(`${CATEGORY_PATH}#product-list`);

        checkTargetIsBelowFixedHeader(TIDs.product_list);
    });

    it('[Mobile Article Anchor] should keep the article gap without rendering the desktop fixed header', () => {
        cy.viewport(1023, 720);
        cy.visitAndWaitForStableAndInteractiveDOM(`${BLOG_ARTICLE_PATH}#${ARTICLE_HEADING_ID}`);

        cy.getByTID([TIDs.fixed_header]).should('not.exist');
        cy.getByTID([TIDs.blog_article_content])
            .find(`#${ARTICLE_HEADING_ID}`)
            .should(($heading) => {
                expect($heading[0].getBoundingClientRect().top).to.be.within(0, 24);
            });
    });
});
