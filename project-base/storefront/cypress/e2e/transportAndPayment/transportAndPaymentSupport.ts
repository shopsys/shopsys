import { TypeTransportWithAvailablePaymentsAndStoresFragment } from '../../../graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { TypeOpeningHoursOfDay, TypeStoreOpeningStatusEnum } from '../../../graphql/types';
import { TIDs } from 'tids';

const getTransportGroupButtonByName = (transportGroupName: string) =>
    cy.getByTID([TIDs.transport_group_button]).contains(transportGroupName).closest('button');

export const openTransportGroupByName = (transportGroupName: string) => {
    getTransportGroupButtonByName(transportGroupName).then(($button) => {
        if ($button.attr('aria-expanded') === 'false') {
            cy.wrap($button).click();
        }
    });

    getTransportGroupButtonByName(transportGroupName)
        .should('have.attr', 'aria-expanded', 'true');

    getTransportGroupButtonByName(transportGroupName)
        .invoke('attr', 'aria-controls')
        .then((transportGroupPanelId) => {
            expect(transportGroupPanelId).to.be.a('string').and.not.be.empty;
            cy.get(`#${transportGroupPanelId}`).should('be.visible');
        });
};

export const chooseTransportPersonalCollectionAndStore = (
    storeUuid: string,
    transportName: string,
    transportGroupName?: string,
) => {
    changeSelectionOfTransportByName(transportName, transportGroupName);
    cy.getByTID([TIDs.layout_popup]).should('be.visible');
    cy.getByTID([[TIDs.store_list_item_, storeUuid]]).then(($storeItem) => {
        if ($storeItem.attr('aria-expanded') === 'false') {
            cy.wrap($storeItem).click();
        }
    });
    cy.getByTID([[TIDs.store_list_item_, storeUuid], TIDs.store_select_button]).click();
    cy.getByTID([TIDs.pages_order_pickupplace_popup_confirm]).should('not.be.disabled').scrollIntoView().click();
};

export const changeSelectionOfTransportByName = (transportName: string, transportGroupName?: string) => {
    cy.get('body').then(($body) => {
        const transportLabelSelector = `[data-tid="${TIDs.pages_order_transport}"] [data-tid="${TIDs.pages_order_selectitem_label_name}"]`;
        const visibleTransportLabel = [...$body.find(transportLabelSelector)].find(
            (transportLabel) =>
                transportLabel.textContent?.includes(transportName) && Cypress.$(transportLabel).is(':visible'),
        );

        if (visibleTransportLabel) {
            cy.wrap(visibleTransportLabel).click('left');

            return;
        }

        if (transportGroupName === undefined) {
            throw new Error(`Transport "${transportName}" is not visible and no transport group was provided.`);
        }

        openTransportGroupByName(transportGroupName);
        cy.getByTID([TIDs.transport_group_panel, TIDs.pages_order_selectitem_label_name])
            .contains(transportName)
            .click('left');
    });
};

export const changeSelectionOfPaymentByName = (paymentName: string) => {
    cy.getByTID([TIDs.pages_order_payment, TIDs.pages_order_selectitem_label_name]).contains(paymentName).click('left');
};

const checkSectionHasEnabledRadios = (sectionTid: TIDs) => {
    cy.getByTID([sectionTid], { timeout: 10000 }).find('input[type="radio"]').its('length').should('be.gte', 1);
    cy.getByTID([sectionTid], { timeout: 10000 }).find('input[type="radio"]:enabled').its('length').should('be.gte', 1);
};

const checkTransportSectionIsInteractive = () => {
    cy.getByTID([TIDs.pages_order_transport], { timeout: 10000 }).should(($transportSection) => {
        const enabledTransportRadiosCount = $transportSection.find('input[type="radio"]:enabled').length;
        const enabledTransportGroupButtonsCount = $transportSection.find(
            `[data-tid="${TIDs.transport_group_button}"]:enabled`,
        ).length;

        expect(enabledTransportRadiosCount + enabledTransportGroupButtonsCount).to.be.gte(1);
    });
};

export const waitForTransportAndPaymentToBeInteractive = () => {
    cy.getByTID([TIDs.loader_overlay], { timeout: 10000 }).should('not.exist');
    checkTransportSectionIsInteractive();

    cy.get('body').then(($body) => {
        const paymentSectionSelector = `[data-tid=${TIDs.pages_order_payment}]`;

        if ($body.find(paymentSectionSelector).length > 0) {
            checkSectionHasEnabledRadios(TIDs.pages_order_payment);
        }
    });
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
