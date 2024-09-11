import { TypeTransportWithAvailablePaymentsAndStoresFragment } from '../../../graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { TypeOpeningHoursOfDay, TypeStoreOpeningStatusEnum } from '../../../graphql/types';
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

export const changeDayOfWeekInTransportsApiResponse = (dayOfWeek: number) => {
    cy.intercept('POST', '/graphql/TransportsFullQuery', (req) => {
        req.reply((response) => {
            response?.body?.data?.transports?.forEach(
                (transport: TypeTransportWithAvailablePaymentsAndStoresFragment) => {
                    transport?.stores?.edges?.forEach((edge) => {
                        if (edge?.node?.openingHours) {
                            edge.node.openingHours.status = 'OPEN' as TypeStoreOpeningStatusEnum;
                            edge.node.openingHours.dayOfWeek = dayOfWeek;
                            edge.node.openingHours.openingHoursOfDays = getStaticOpeningHoursOfDays();
                        }
                    });
                },
            );
        });
    });
};

export const changeDayOfWeekInChangeTransportMutationResponse = (dayOfWeek: number) => {
    cy.intercept('POST', '/graphql/ChangeTransportInCartMutation', (req) => {
        req.reply((response) => {
            (
                response?.body?.data?.ChangeTransportInCart
                    ?.transport as TypeTransportWithAvailablePaymentsAndStoresFragment
            )?.stores?.edges?.forEach((edge) => {
                if (edge?.node?.openingHours) {
                    edge.node.openingHours.status = 'OPEN' as TypeStoreOpeningStatusEnum;
                    edge.node.openingHours.dayOfWeek = dayOfWeek;
                    edge.node.openingHours.openingHoursOfDays = getStaticOpeningHoursOfDays();
                }
            });
        });
    });
};

const getStaticOpeningHoursOfDays = (): TypeOpeningHoursOfDay[] => [
    {
        __typename: 'OpeningHoursOfDay',
        date: '2024-02-19T00:00:00+01:00',
        dayOfWeek: 1,
        openingHoursRanges: [
            {
                __typename: 'OpeningHoursRange',
                openingTime: '06:00',
                closingTime: '11:00',
            },
            {
                __typename: 'OpeningHoursRange',
                openingTime: '13:00',
                closingTime: '15:00',
            },
        ],
    },
    {
        __typename: 'OpeningHoursOfDay',
        date: '2024-02-20T00:00:00+01:00',
        dayOfWeek: 2,
        openingHoursRanges: [
            {
                __typename: 'OpeningHoursRange',
                openingTime: '07:00',
                closingTime: '11:00',
            },
            {
                __typename: 'OpeningHoursRange',
                openingTime: '13:00',
                closingTime: '15:00',
            },
        ],
    },
    {
        __typename: 'OpeningHoursOfDay',
        date: '2024-02-21T00:00:00+01:00',
        dayOfWeek: 3,
        openingHoursRanges: [
            {
                __typename: 'OpeningHoursRange',
                openingTime: '08:00',
                closingTime: '11:00',
            },
            {
                __typename: 'OpeningHoursRange',
                openingTime: '13:00',
                closingTime: '15:00',
            },
        ],
    },
    {
        __typename: 'OpeningHoursOfDay',
        date: '2024-02-22T00:00:00+01:00',
        dayOfWeek: 4,
        openingHoursRanges: [
            {
                __typename: 'OpeningHoursRange',
                openingTime: '09:00',
                closingTime: '11:00',
            },
            {
                __typename: 'OpeningHoursRange',
                openingTime: '13:00',
                closingTime: '15:00',
            },
        ],
    },
    {
        __typename: 'OpeningHoursOfDay',
        date: '2024-02-23T00:00:00+01:00',
        dayOfWeek: 5,
        openingHoursRanges: [
            {
                __typename: 'OpeningHoursRange',
                openingTime: '10:00',
                closingTime: '11:00',
            },
            {
                __typename: 'OpeningHoursRange',
                openingTime: '13:00',
                closingTime: '15:00',
            },
        ],
    },
    {
        __typename: 'OpeningHoursOfDay',
        date: '2024-02-24T00:00:00+01:00',
        dayOfWeek: 6,
        openingHoursRanges: [
            {
                __typename: 'OpeningHoursRange',
                openingTime: '08:00',
                closingTime: '11:00',
            },
        ],
    },
    {
        __typename: 'OpeningHoursOfDay',
        date: '2024-02-25T00:00:00+01:00',
        dayOfWeek: 7,
        openingHoursRanges: [
            {
                __typename: 'OpeningHoursRange',
                openingTime: '09:00',
                closingTime: '11:00',
            },
        ],
    },
];

export const removePaymentSelectionUsingButton = () => {
    cy.getByTID([TIDs.reset_payment_button]).click();
};

export const removeTransportSelectionUsingButton = () => {
    cy.getByTID([TIDs.reset_transport_button]).click();
};
