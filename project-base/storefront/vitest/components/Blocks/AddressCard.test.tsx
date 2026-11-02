import { render, screen } from '@testing-library/react';
import { AddressCard } from 'components/Blocks/AddressList/AddressCard';
import { DeliveryAddressType } from 'types/customer';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canManagePersonalData: false }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

const address: DeliveryAddressType = {
    uuid: 'delivery-address',
    firstName: 'Jane',
    lastName: 'Doe',
    companyName: '',
    street: 'Delivery street 2',
    city: 'Brno',
    postcode: '60200',
    country: { code: 'CZ', name: 'Czech Republic' },
    telephone: '+420123456789',
    telephonePrefix: '+420',
    telephonePrefixCountryCode: 'CZ',
    telephoneNumber: '123456789',
};

describe('AddressCard', () => {
    test('exposes the order selection independently of the default address badge', () => {
        const selectAddress = vi.fn();
        const props = { address, defaultDeliveryAddress: address, orderSelectAddressHandler: selectAddress };
        const { rerender } = render(<AddressCard {...props} orderSelectedAddress={false} />);

        expect(screen.getByText('Default address')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Set this address as the delivery address for the order' }),
        ).toHaveAttribute('aria-pressed', 'false');

        rerender(<AddressCard {...props} orderSelectedAddress />);

        expect(
            screen.getByRole('button', { name: 'Set this address as the delivery address for the order' }),
        ).toHaveAttribute('aria-pressed', 'true');

        rerender(<AddressCard {...props} orderSelectedAddress={false} />);

        expect(
            screen.getByRole('button', { name: 'Set this address as the delivery address for the order' }),
        ).toHaveAttribute('aria-pressed', 'false');
    });

    test('does not expose an order selection outside checkout', () => {
        render(<AddressCard address={address} defaultDeliveryAddress={address} />);

        expect(screen.getByRole('button', { name: /Jane Doe/ })).not.toHaveAttribute('aria-pressed');
    });
});
