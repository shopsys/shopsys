import { render, screen } from '@testing-library/react';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import type React from 'react';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const currentFilterQuery = { flags: ['active-flag'] };
const sessionStore = { defaultProductFiltersMap: { flags: new Set<string>() } };

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: typeof sessionStore) => unknown) => selector(sessionStore),
}));

vi.mock('components/Basic/Flag/Flag', () => ({
    Flag: ({ children }: { children: React.ReactNode }) => <div data-testid="product-flag">{children}</div>,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/queryParams/useCurrentFilterQuery', () => ({
    useCurrentFilterQuery: () => currentFilterQuery,
}));

const flags = [
    { uuid: 'first-flag', name: 'First', rgbColor: '#000000' },
    { uuid: 'active-flag', name: 'Active', rgbColor: '#111111' },
    { uuid: 'third-flag', name: 'Third', rgbColor: '#222222' },
    { uuid: 'fourth-flag', name: 'Fourth', rgbColor: '#333333' },
    { uuid: 'fifth-flag', name: 'Fifth', rgbColor: '#444444' },
    { uuid: 'sixth-flag', name: 'Sixth', rgbColor: '#555555' },
] as any;

describe('ProductFlags', () => {
    beforeEach(() => {
        currentFilterQuery.flags = ['active-flag'];
        sessionStore.defaultProductFiltersMap.flags = new Set();
    });

    test('prioritizes SEO category flags even without a filter in the URL', () => {
        currentFilterQuery.flags = [];
        sessionStore.defaultProductFiltersMap.flags.add('sixth-flag');

        render(<ProductFlags flags={flags} percentageDiscount={null} variant="gridHeader" />);

        expect(screen.getAllByTestId('product-flag').map((flag) => flag.textContent)).toEqual([
            'Sixth',
            'First',
            'Active',
            'Third',
            'Fourth',
        ]);
    });

    test('combines SEO and URL flags without duplicates before the discount', () => {
        sessionStore.defaultProductFiltersMap.flags = new Set(['active-flag', 'sixth-flag']);

        render(
            <ProductFlags
                flags={flags}
                percentageDiscount={25}
                variant="gridHeader"
                visibleItemsConfig={{ flags: true, discount: true }}
            />,
        );

        expect(screen.getAllByTestId('product-flag').map((flag) => flag.textContent)).toEqual([
            'Active',
            'Sixth',
            '-25% disount',
            'First',
            'Third',
        ]);
    });

    test('shows the active filter, discount and remaining flags in this order', () => {
        render(
            <ProductFlags
                flags={flags}
                percentageDiscount={25}
                variant="gridHeader"
                visibleItemsConfig={{ flags: true, discount: true }}
            />,
        );

        expect(screen.getAllByTestId('product-flag').map((flag) => flag.textContent)).toEqual([
            'Active',
            '-25% disount',
            'First',
            'Third',
            'Fourth',
        ]);
        expect(screen.queryByText('Fifth')).not.toBeInTheDocument();
    });

    test('keeps bestseller flags in administration order after the discount despite an active filter', () => {
        sessionStore.defaultProductFiltersMap.flags.add('sixth-flag');
        render(
            <ProductFlags
                flags={flags}
                percentageDiscount={25}
                variant="bestsellers"
                visibleItemsConfig={{ flags: true, discount: true }}
            />,
        );

        expect(screen.getAllByTestId('product-flag').map((flag) => flag.textContent)).toEqual([
            '-25% disount',
            'First',
            'Active',
            'Third',
            'Fourth',
        ]);
    });

    test('positions grid header flags absolutely over the reserved card space', () => {
        render(
            <ProductFlags
                flags={flags}
                percentageDiscount={null}
                variant="gridHeader"
                visibleItemsConfig={{ flags: true, discount: false }}
            />,
        );

        expect(screen.getAllByTestId('product-flag')[0].parentElement).toHaveClass('absolute', 'top-0', 'left-0');
        expect(screen.getAllByTestId('product-flag')).toHaveLength(5);
        expect(screen.queryByText('Sixth')).not.toBeInTheDocument();
    });

    test('shows at most five comparison flags in administration order', () => {
        sessionStore.defaultProductFiltersMap.flags.add('sixth-flag');
        render(<ProductFlags flags={flags} percentageDiscount={null} variant="comparison" />);

        expect(screen.getAllByTestId('product-flag').map((flag) => flag.textContent)).toEqual([
            'First',
            'Active',
            'Third',
            'Fourth',
            'Fifth',
        ]);
        expect(screen.queryByText('Sixth')).not.toBeInTheDocument();
    });
});
