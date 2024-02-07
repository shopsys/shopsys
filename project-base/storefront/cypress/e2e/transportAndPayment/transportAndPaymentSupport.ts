import { CyHttpMessages } from 'cypress/types/net-stubbing';
import { DataTestIds } from 'dataTestIds';
import { transport } from 'fixtures/demodata';

export const chooseTransportPersonalCollectionAndStore = (storeName: string) => {
    cy.getByDataTestId([DataTestIds.pages_order_selectitem_label_name])
        .contains(transport.personalCollection.name)
        .click();
    cy.getByDataTestId([DataTestIds.layout_popup]);
    cy.getByDataTestId([DataTestIds.pages_order_selectitem_label_name]).contains(storeName).click();
    cy.getByDataTestId([DataTestIds.pages_order_pickupplace_popup_confirm]).scrollIntoView().click();
};

export const changeSelectionOfTransportByName = (transportName: string) => {
    cy.getByDataTestId([DataTestIds.pages_order_transport, DataTestIds.pages_order_selectitem_label_name])
        .contains(transportName)
        .click('left');
};

export const changeSelectionOfPaymentByName = (paymentName: string) => {
    cy.getByDataTestId([DataTestIds.pages_order_payment, DataTestIds.pages_order_selectitem_label_name])
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
