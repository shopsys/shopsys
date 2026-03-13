import { staticData, url } from 'fixtures/demodata';
import { changeElementText, checkUrl } from 'support';
import { TIDs } from 'tids';

export const navigateToStoresFromHeader = () => {
    cy.getByTID([TIDs.header_stores_link]).should('be.visible').click();
    checkUrl(url.stores);
    cy.waitForStableAndInteractiveDOM();
};

export const changeStoreOpeningHoursToStaticDemodata = () => {
    changeElementText(TIDs.store_opening_hours, staticData.openingHours, false);
};

export const changeStoreOpeningStatusToStaticDemodata = () => {
    changeElementText(TIDs.store_opening_status, staticData.openingStatus, false);
};

export const expandFirstStoreAndClickDetail = () => {
    // Store items are collapsed by default — click the first one to expand it
    cy.getByTID([TIDs.store_list]).find('[aria-expanded="false"]').first().click();
    cy.waitForStableAndInteractiveDOM();

    cy.getByTID([TIDs.store_detail_link]).first().click();
    cy.waitForStableAndInteractiveDOM();
};
