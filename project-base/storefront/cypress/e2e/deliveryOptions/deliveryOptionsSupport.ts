import { TypeProductDeliveryStoresQuery } from '../../../graphql/requests/transports/queries/ProductDeliveryStoresQuery.generated';
import { TypeStoreOpeningStatusEnum } from '../../../graphql/types';
import { getStaticOpeningHoursOfDays } from '../transportAndPayment/transportAndPaymentSupport';
import { TIDs } from 'tids';

export const changeDayOfWeekInProductDeliveryStoresApiResponse = (dayOfWeek: number) => {
    cy.intercept('POST', '/graphql/ProductDeliveryStoresQuery', (req) => {
        req.reply((response) => {
            const stores = (response?.body?.data as TypeProductDeliveryStoresQuery | undefined)?.productDeliveryStores;

            stores?.edges?.forEach((edge) => {
                if (edge?.node?.store.openingHours) {
                    edge.node.store.openingHours.status = 'OPEN' as TypeStoreOpeningStatusEnum;
                    edge.node.store.openingHours.dayOfWeek = dayOfWeek;
                    edge.node.store.openingHours.openingHoursOfDays = getStaticOpeningHoursOfDays();
                }
            });
        });
    });
};

export const openDeliveryOptionsPopupUsingLink = () => {
    cy.getByTID([TIDs.product_detail_delivery_options_link]).should('be.visible').click();
    cy.getByTID([TIDs.product_detail_delivery_options_popup]).should('be.visible');
    cy.waitForStableAndInteractiveDOM();
};

export const openDeliveryOptionsPopupUsingVariantAvailability = (variantCatnum: string) => {
    cy.getByTID([[TIDs.pages_productdetail_variant_, variantCatnum], TIDs.product_availability])
        .should('be.visible')
        .click();
    cy.getByTID([TIDs.product_detail_delivery_options_popup]).should('be.visible');
    cy.waitForStableAndInteractiveDOM();
};

export const chooseVariantInDeliveryOptionsPopup = (variantUuid: string) => {
    cy.getByTID([TIDs.delivery_options_variant_select]).click();
    cy.getByTID([[TIDs.delivery_options_variant_option_, variantUuid]]).should('be.visible').click();
    cy.waitForStableAndInteractiveDOM();
};

export const checkVariantSelectShowsProductName = (productName: string) => {
    cy.getByTID([TIDs.delivery_options_variant_select]).should('contain.text', productName);
};

export const checkDeliveryOptionsPanelsAreVisible = () => {
    cy.getByTID([TIDs.delivery_options_address_panel]).should('be.visible');
    cy.getByTID([TIDs.delivery_options_pickup_panel]).should('be.visible');
};

export const checkDeliveryOptionsPanelsAreNotPresent = () => {
    cy.getByTID([TIDs.delivery_options_address_panel]).should('not.exist');
    cy.getByTID([TIDs.delivery_options_pickup_panel]).should('not.exist');
};
