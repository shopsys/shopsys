import { fireEvent, render, screen } from '@testing-library/react';
import { ProductDetailAvailability } from 'components/Pages/ProductDetail/ProductDetailAvailability';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

const openDeliveryOptionsPopupMock = vi.hoisted(() => vi.fn());

vi.mock('components/Blocks/Popup/DeliveryOptionsPopup/useOpenDeliveryOptionsPopup', () => ({
    useOpenDeliveryOptionsPopup: () => openDeliveryOptionsPopupMock,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: { count?: number }) =>
            key
                .replace('{{ count }}', String(options?.count))
                .replace('stores', options?.count === 1 ? 'store' : 'stores'),
    }),
}));

const product = {
    uuid: '00000000-0000-0000-0000-000000000001',
    availability: {
        name: 'Out of stock',
        status: TypeAvailabilityStatusEnum.OutOfStock,
    },
    availableStoresCount: null,
    isCurrentlyOutOfStock: false,
    isInquiryType: false,
    isMainVariant: false,
    isSellingDenied: false,
    isVisible: true,
} as unknown as TypeProductDetailFragment;

describe('ProductDetailAvailability', () => {
    test('shows the out-of-stock status even when the product can be ordered', () => {
        render(<ProductDetailAvailability product={product} />);

        expect(screen.getByText('Out of stock')).toBeInTheDocument();
    });

    test('opens delivery options for the product when availability is clicked', () => {
        render(<ProductDetailAvailability product={product} />);

        const availabilityButton = screen.getByRole('button', { name: 'Out of stock' });
        fireEvent.click(availabilityButton);

        expect(availabilityButton).toHaveAttribute('aria-haspopup', 'dialog');
        expect(openDeliveryOptionsPopupMock).toHaveBeenCalledWith([product], product.uuid);
    });

    test('shows shipping readiness and store availability as separate facts', () => {
        render(
            <ProductDetailAvailability
                product={{
                    ...product,
                    availability: {
                        __typename: 'Availability',
                        name: 'In stock',
                        status: TypeAvailabilityStatusEnum.InStock,
                    },
                    availableStoresCount: 1,
                }}
            />,
        );

        expect(screen.getByText('In stock')).toBeInTheDocument();
        expect(screen.getByText('Ready to ship · 1 store')).toBeInTheDocument();
    });

    test('does not show store availability when no store has the product', () => {
        render(
            <ProductDetailAvailability
                product={{
                    ...product,
                    availability: {
                        __typename: 'Availability',
                        name: 'In stock',
                        status: TypeAvailabilityStatusEnum.InStock,
                    },
                    availableStoresCount: 0,
                }}
            />,
        );

        expect(screen.getByText('Ready to ship')).toBeInTheDocument();
        expect(screen.queryByText(/store/)).not.toBeInTheDocument();
    });
});
