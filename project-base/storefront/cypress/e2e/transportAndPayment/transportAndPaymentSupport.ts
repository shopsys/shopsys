import { CyHttpMessages } from 'cypress/types/net-stubbing';
import { transport } from 'fixtures/demodata';

export const chooseTransportPersonalCollectionAndStore = (storeName: string) => {
    cy.getByDataTestId('pages-order-selectitem-label-name').contains(transport.personalCollection.name).click();
    cy.getByDataTestId('layout-popup');
    cy.getByDataTestId('pages-order-selectitem-label-name').contains(storeName).click();
    cy.getByDataTestId('pages-order-pickupplace-popup-confirm').scrollIntoView().click();
};

export const changeSelectionOfTransportByName = (transportName: string) => {
    cy.getByDataTestId(['pages-order-transport', 'pages-order-selectitem-label-name'])
        .contains(transportName)
        .click('left');
};

export const changeSelectionOfPaymentByName = (paymentName: string) => {
    cy.getByDataTestId(['pages-order-payment', 'pages-order-selectitem-label-name'])
        .contains(paymentName)
        .click('left');
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
