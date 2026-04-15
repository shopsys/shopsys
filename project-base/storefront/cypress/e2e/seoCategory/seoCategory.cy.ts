import { staticData } from 'fixtures/demodata';
import {
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { visitEntityByUuid } from 'support/navigation';
import { t } from 'support/translations';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.SEO_CATEGORY, SUBGROUP_INDEX);

describe('SEO Category Tests (SSP-1742)', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('[SEO Category Links Visible] should display SEO category links on a category page', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.waitForStableAndInteractiveDOM();

        cy.getByTID([TIDs.seo_categories]).should('be.visible');
        cy.getByTID([TIDs.seo_categories]).find('a').should('have.length.greaterThan', 0);
    });

    it('[SEO Category Navigation] should click an SEO category link and verify URL changes with filtered products', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.waitForStableAndInteractiveDOM();

        let originalUrl: string;
        cy.url().then((currentUrl) => {
            originalUrl = currentUrl;
        });

        cy.getByTID([TIDs.seo_categories])
            .find('a')
            .first()
            .then(($link) => {
                const linkText = $link.text();

                cy.wrap($link).click();
                cy.waitForStableAndInteractiveDOM();

                cy.url().should('not.equal', originalUrl);
                cy.getByTID([TIDs.page_title]).should('be.visible').and('contain.text', linkText);
                cy.getByTID([TIDs.product_list_item_image]).should('exist');

                takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'seo category detail page', {
                    blackout: [
                        { tid: TIDs.simple_navigation_image },
                        { tid: TIDs.product_list_item_image },
                        { tid: TIDs.category_bestseller_image },
                        { tid: TIDs.seo_categories },
                        { tid: TIDs.footer_social_links },
                        { tid: TIDs.footer_payment_images },
                        { tid: TIDs.footer_copyright },
                    ],
                });
            });
    });

    it('[SEO Category Filter Removal] should verify that removing a filter from an SEO category changes URL and heading', () => {
        visitEntityByUuid('category', staticData.categories.electronics.uuid);
        cy.waitForStableAndInteractiveDOM();

        t('Electronics in black').then((translatedName) => {
            cy.getByTID([TIDs.seo_categories])
                .find('a')
                .contains(translatedName)
                .then(($link) => {
                    const linkText = $link.text();

                    cy.wrap($link).click();
                    cy.waitForStableAndInteractiveDOM();

                    let seoCategoryUrl = '';
                    cy.url().then((currentUrl) => {
                        seoCategoryUrl = currentUrl;
                    });

                    cy.getByTID([TIDs.page_title]).should('contain.text', linkText);
                    cy.getByTID([TIDs.selected_filters]).should('be.visible');

                    cy.getByTID([TIDs.selected_filters]).find('button').first().click({ force: true });
                    cy.waitForStableAndInteractiveDOM();

                    cy.url().should('not.equal', seoCategoryUrl);
                    cy.getByTID([TIDs.page_title]).should('not.contain.text', linkText);
                });
        });
    });
});
