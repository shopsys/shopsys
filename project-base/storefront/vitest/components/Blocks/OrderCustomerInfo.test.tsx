import { render, screen } from '@testing-library/react';
import { OrderCustomerInfo } from 'components/Blocks/OrderCustomerInfo/OrderCustomerInfo';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children }: { children: React.ReactNode }) => <span>{children}</span>,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

const createOrder = (transportTypeCode: string): TypeOrderDetailFragment =>
    ({
        firstName: 'John',
        lastName: 'Doe',
        email: 'john.doe@example.com',
        telephone: '+420123456789',
        companyName: 'Billing company',
        companyNumber: null,
        companyTaxNumber: null,
        street: 'Billing street 1',
        city: 'Prague',
        postcode: '11000',
        country: { name: 'Czech Republic' },
        deliveryFirstName: 'Jane',
        deliveryLastName: 'Doe',
        deliveryCompanyName: null,
        deliveryTelephone: '+420987654321',
        deliveryStreet: 'Delivery street 2',
        deliveryCity: 'Brno',
        deliveryPostcode: '60200',
        deliveryCountry: { name: 'Czech Republic' },
        items: [
            {
                type: TypeOrderItemTypeEnum.Transport,
                transport: { transportTypeCode },
            },
        ],
    }) as TypeOrderDetailFragment;

describe('OrderCustomerInfo', () => {
    test('shows email delivery information instead of a delivery address for email transport', () => {
        render(<OrderCustomerInfo order={createOrder('email')} />);

        expect(screen.getByText('Delivered by email')).toBeInTheDocument();
        expect(screen.queryByText('Delivery street 2')).not.toBeInTheDocument();
        expect(screen.getByText('Billing street 1')).toBeInTheDocument();
    });

    test('shows the delivery address for a regular transport', () => {
        render(<OrderCustomerInfo order={createOrder('common')} />);

        expect(screen.queryByText('Delivered by email')).not.toBeInTheDocument();
        expect(screen.getByText('Delivery street 2')).toBeInTheDocument();
    });
});
