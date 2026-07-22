import { render, screen } from '@testing-library/react';
import { AdditionalServiceSummaryList } from 'components/Blocks/Product/AdditionalServices/AdditionalServiceSummaryList';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import {
    mapCartItemAdditionalServiceSummaryLines,
    mapOrderItemAdditionalServiceSummaryLines,
} from 'utils/mappers/additionalServices';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string | number>) => {
            if (key === '+{{ count }} working days' && options?.count === 1) {
                return '+1 working day';
            }

            return Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey.replaceAll(`{{ ${optionKey} }}`, String(optionValue)),
                key,
            );
        },
    }),
}));

const createAdditionalService = (
    uuid: string,
    deliveryDaysExtension: number | null,
): TypeAdditionalServiceFragment => ({
    __typename: 'AdditionalService',
    id: 1,
    uuid,
    name: `Service ${uuid}`,
    catnum: `SERVICE-${uuid}`,
    description: null,
    deliveryDaysExtension,
    mainImage: null,
    price: {
        __typename: 'Price',
        priceWithVat: '10',
        priceWithoutVat: '8',
        vatAmount: '2',
    },
});

const createOrderItemAdditionalService = (
    uuid: string,
    deliveryDaysExtension: number | null,
): TypeOrderDetailItemFragment['relatedItems'][number] => ({
    __typename: 'OrderItem',
    uuid,
    name: `Service ${uuid}`,
    catnum: `SERVICE-${uuid}`,
    deliveryDaysExtension,
    quantity: 2,
    unit: 'pcs',
    type: TypeOrderItemTypeEnum.AdditionalService,
    mainImage: null,
    unitPrice: {
        __typename: 'Price',
        priceWithVat: '10',
        priceWithoutVat: '8',
        vatAmount: '2',
    },
    totalPrice: {
        __typename: 'Price',
        priceWithVat: '20',
        priceWithoutVat: '16',
        vatAmount: '4',
    },
});

describe('AdditionalServiceSummaryList', () => {
    test('shows delivery extension only for services that have it', () => {
        const services = mapCartItemAdditionalServiceSummaryLines(
            [createAdditionalService('with-extension', 1), createAdditionalService('without-extension', null)],
            2,
            'pcs',
            (price) => `€${price}`,
        );

        render(<AdditionalServiceSummaryList services={services} />);

        expect(screen.getByText('+1 working day')).toBeInTheDocument();
        expect(screen.getAllByText('€20')[0]).toHaveClass('text-price-default');
        expect(screen.getAllByText('€20')[0]).not.toHaveClass('text-text-default');
        expect(screen.getByText('Service with-extension').parentElement).toContainElement(
            screen.getByText('+1 working day'),
        );
        expect(screen.getByText('Service without-extension').parentElement).not.toHaveTextContent('working days');
    });

    test('shows delivery extension for services mapped from an order', () => {
        const services = mapOrderItemAdditionalServiceSummaryLines(
            [createOrderItemAdditionalService('with-extension', 1)],
            (price) => `€${price}`,
        );

        render(<AdditionalServiceSummaryList isPriceHighlighted={false} services={services} />);

        expect(screen.getByText('Service with-extension').parentElement).toContainElement(
            screen.getByText('+1 working day'),
        );
        expect(screen.getByText('€20')).toHaveClass('text-text-default');
        expect(screen.getByText('€20')).not.toHaveClass('text-price-default');
    });
});
