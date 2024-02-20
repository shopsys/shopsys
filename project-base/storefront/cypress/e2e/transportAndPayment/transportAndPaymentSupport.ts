import {
    OpeningHoursFragmentApi,
    OpeningHoursOfDayApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from '../../../graphql/generated/index';
import { CyHttpMessages } from 'cypress/types/net-stubbing';
import { transport } from 'fixtures/demodata';
import { TIDs } from 'tids';

export const chooseTransportPersonalCollectionAndStore = (storeName: string) => {
    cy.getByTID([TIDs.pages_order_selectitem_label_name]).contains(transport.personalCollection.name).click();
    cy.getByTID([TIDs.layout_popup]);
    cy.getByTID([TIDs.pages_order_selectitem_label_name]).contains(storeName).click();
    cy.getByTID([TIDs.pages_order_pickupplace_popup_confirm]).scrollIntoView().click();
};

export const changeSelectionOfTransportByName = (transportName: string) => {
    cy.getByTID([TIDs.pages_order_transport, TIDs.pages_order_selectitem_label_name])
        .contains(transportName)
        .click('left');
};

export const changeSelectionOfPaymentByName = (paymentName: string) => {
    cy.getByTID([TIDs.pages_order_payment, TIDs.pages_order_selectitem_label_name]).contains(paymentName).click('left');
};

export type Transport = {
    stores: { edges: ({ node: { openingHours: { dayOfWeek: number } } | null } | null)[] | null } | null;
};
export const changeDayOfWeekInApiResponses = (dayOfWeek: number) => {
    cy.intercept('POST', '/graphql/', (req) => {
        req.reply((response) => {
            tryChangingDayOfWeekInTransportsApiResponse(response, dayOfWeek);
            tryChangingDayOfWeekInChangeTransportMutationApiResponse(response, dayOfWeek);
        });
    });
};

const tryChangingDayOfWeekInTransportsApiResponse = (
    response: CyHttpMessages.IncomingHttpResponse,
    dayOfWeek: number,
) => {
    response?.body?.data?.transports?.forEach((transport: Transport) => {
        transport?.stores?.edges?.forEach((edge) => {
            if (edge?.node?.openingHours) {
                edge.node.openingHours.dayOfWeek = dayOfWeek;
            }
        });
    });
};

const tryChangingDayOfWeekInChangeTransportMutationApiResponse = (
    response: CyHttpMessages.IncomingHttpResponse,
    dayOfWeek: number,
) => {
    (response?.body?.data?.ChangeTransportInCart?.transport as Transport)?.stores?.edges?.forEach((edge) => {
        if (edge?.node?.openingHours) {
            edge.node.openingHours.dayOfWeek = dayOfWeek;
        }
    });
};
