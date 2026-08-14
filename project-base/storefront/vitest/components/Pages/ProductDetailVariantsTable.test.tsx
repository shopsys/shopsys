import { fireEvent, render, screen } from '@testing-library/react';
import { ProductVariantsTable } from 'components/Pages/ProductDetail/ProductDetailVariantsTable';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { PropsWithChildren } from 'react';
import { describe, expect, test, vi } from 'vitest';

const openDeliveryOptionsPopupMock = vi.hoisted(() => vi.fn());

vi.mock('components/Basic/Image/Image', () => ({
    Image: () => null,
}));

vi.mock('components/Blocks/Popup/DeliveryOptionsPopup/useOpenDeliveryOptionsPopup', () => ({
    useOpenDeliveryOptionsPopup: () => openDeliveryOptionsPopupMock,
}));

vi.mock('components/Blocks/Product/ProductAction', () => ({
    PRODUCT_VARIANTS_ID: 'product-variants',
    ProductAction: () => null,
}));

vi.mock('components/Blocks/Product/ProductPrice', () => ({
    ProductPrice: () => null,
}));

vi.mock('components/Blocks/Product/Watchdog/WatchDogButton', () => ({
    showWatchdogButton: () => false,
    WatchDogButton: () => null,
}));

vi.mock('components/Layout/Webline/Webline', () => ({
    Webline: ({ children }: PropsWithChildren) => <div>{children}</div>,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: { count?: number }) =>
            key
                .replace('{{ count }}', String(options?.count))
                .replace('stores', options?.count === 1 ? 'store' : 'stores'),
    }),
}));

const variant = {
    uuid: '00000000-0000-0000-0000-000000000001',
    catalogNumber: '123456',
    fullName: 'Test variant',
    mainImage: null,
    isCurrentlyOutOfStock: false,
    isMainVariant: false,
    isSellingDenied: false,
    isInquiryType: false,
    isVisible: true,
    availability: {
        name: 'In stock',
        status: TypeAvailabilityStatusEnum.InStock,
    },
    availableStoresCount: 1,
    price: {
        priceWithVat: '100',
    },
} as unknown as TypeMainVariantDetailFragment['variants'][number];

describe('ProductVariantsTable', () => {
    test('opens delivery options with the clicked variant preselected', () => {
        render(<ProductVariantsTable deliveryOptionsProducts={[variant]} variants={[variant]} />);

        fireEvent.click(screen.getByRole('button', { name: /In stock/ }));

        expect(openDeliveryOptionsPopupMock).toHaveBeenCalledWith([variant], variant.uuid);
    });

    test('shows underlined stock details for a clickable variant', () => {
        render(<ProductVariantsTable deliveryOptionsProducts={[variant]} variants={[variant]} />);

        expect(screen.getByText(/Ready to ship.*1 store/)).toHaveClass('underline');
    });

    test('keeps availability non-interactive for an unsellable variant', () => {
        const unsellableVariant = { ...variant, isCurrentlyOutOfStock: true };

        render(<ProductVariantsTable deliveryOptionsProducts={[]} variants={[unsellableVariant]} />);

        expect(screen.getByText('In stock')).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: /In stock/ })).not.toBeInTheDocument();
    });
});
