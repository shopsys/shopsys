import { TypeStoreDetailQuery } from '../../../graphql/requests/stores/queries/StoreDetailQuery.generated';
import { TypeStoresQuery } from '../../../graphql/requests/stores/queries/StoresQuery.generated';
import { TypeStoreOpeningStatusEnum } from '../../../graphql/types';
import { getStaticOpeningHoursOfDays } from '../transportAndPayment/transportAndPaymentSupport';
import { url } from 'fixtures/demodata';
import { checkUrl, getHeaderElementByTID } from 'support';
import { TIDs } from 'tids';
import type { SSRData } from 'urql';

export const navigateToStoresFromHeader = () => {
    getHeaderElementByTID(TIDs.header_stores_link).should('be.visible').click();
    checkUrl(url.stores);
    cy.waitForStableAndInteractiveDOM();
};

const setStaticStoreOpeningHours = (data: Partial<TypeStoresQuery & TypeStoreDetailQuery>) => {
    const openingHours = data.stores?.edges?.map((edge) => edge?.node?.openingHours) ?? [];
    if (data.store) {
        openingHours.push(data.store.openingHours);
    }

    openingHours.forEach((hours) => {
        if (hours) {
            hours.status = 'OPEN' as TypeStoreOpeningStatusEnum;
            hours.dayOfWeek = 1;
            hours.openingHoursOfDays = getStaticOpeningHoursOfDays();
        }
    });
};

export const useStaticStoreOpeningHours = () => {
    cy.intercept('POST', /\/graphql\/(StoresQuery|StoreDetailQuery)$/, (req) => {
        req.reply((response) => {
            setStaticStoreOpeningHours(response.body.data);
        });
    });

    // Store pages are prefetched on the server, so client GraphQL interception alone is insufficient.
    cy.intercept('GET', '**/_next/data/**/*.json*', (req) => {
        req.reply((response) => {
            const urqlState: SSRData = response.body.pageProps?.urqlState ?? {};
            Object.values(urqlState).forEach((entry) => {
                if (entry?.data) {
                    const data = JSON.parse(entry.data);
                    setStaticStoreOpeningHours(data);
                    entry.data = JSON.stringify(data);
                }
            });
        });
    });
};

export const expandFirstStoreAndClickDetail = () => {
    // Store items are collapsed by default — click the first one to expand it
    cy.getByTID([TIDs.store_list]).find('[aria-expanded="false"]').first().click();
    cy.waitForStableAndInteractiveDOM();

    cy.getByTID([TIDs.store_detail_link]).first().click();
    cy.waitForStableAndInteractiveDOM();
};
