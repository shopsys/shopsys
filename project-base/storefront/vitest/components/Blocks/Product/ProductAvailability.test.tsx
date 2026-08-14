import { render, screen } from '@testing-library/react';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: { count?: number }) =>
            key
                .replace('{{ count }}', String(options?.count))
                .replace('stores', options?.count === 1 ? 'store' : 'stores'),
    }),
}));

const inStockAvailability = {
    name: 'In stock',
    status: TypeAvailabilityStatusEnum.InStock,
} as TypeAvailability;

describe('ProductAvailability', () => {
    test('shows shipping readiness together with the store count in the default display', () => {
        render(
            <ProductAvailability availability={inStockAvailability} availableStoresCount={1} isInquiryType={false} />,
        );

        expect(screen.getByText('In stock')).toBeInTheDocument();
        expect(screen.getByText('Ready to ship · 1 store')).toBeInTheDocument();
    });

    test('does not show shipping readiness for an out-of-stock product', () => {
        render(
            <ProductAvailability
                availability={{
                    name: 'Out of stock',
                    status: TypeAvailabilityStatusEnum.OutOfStock,
                }}
                availableStoresCount={null}
                isInquiryType={false}
            />,
        );

        expect(screen.getByText('Out of stock')).toBeInTheDocument();
        expect(screen.queryByText(/Ready to ship/)).not.toBeInTheDocument();
    });

    test('does not promise shipping readiness when store availability is unknown', () => {
        render(
            <ProductAvailability
                availability={inStockAvailability}
                availableStoresCount={null}
                isInquiryType={false}
            />,
        );

        expect(screen.getByText('In stock')).toBeInTheDocument();
        expect(screen.queryByText(/Ready to ship/)).not.toBeInTheDocument();
    });

    test('keeps store availability details in the compact display', () => {
        render(
            <ProductAvailability
                availability={inStockAvailability}
                availableStoresCount={3}
                displayMode="compact"
                isInquiryType={false}
            />,
        );

        expect(screen.getByText('In stock, Ready to ship · 3 stores')).toBeInTheDocument();
    });
});
