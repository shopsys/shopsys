import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ProductListViewModeType } from 'components/Blocks/Product/ProductsList/ProductListItem';
import { ProductListViewModeToggle } from 'components/Blocks/Product/ProductsList/ProductListViewModeToggle';
import { beforeEach, describe, expect, test, vi } from 'vitest';

type TestCookiesStore = {
    productListViewMode: ProductListViewModeType;
    setCookiesStoreState: (value: Partial<{ productListViewMode: ProductListViewModeType }>) => void;
};

const { productListViewModeState, setCookiesStoreStateMock } = vi.hoisted(() => ({
    productListViewModeState: { value: 'grid' as ProductListViewModeType },
    setCookiesStoreStateMock: vi.fn(),
}));

vi.mock('components/Basic/Icon/GridIcon', () => ({
    GridIcon: () => null,
}));

vi.mock('components/Basic/Icon/ListIcon', () => ({
    ListIcon: () => null,
}));

vi.mock('store/useCookiesStore', () => ({
    useCookiesStore: (selector: (store: TestCookiesStore) => unknown) =>
        selector({
            productListViewMode: productListViewModeState.value,
            setCookiesStoreState: setCookiesStoreStateMock,
        }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

describe('ProductListViewModeToggle', () => {
    beforeEach(() => {
        productListViewModeState.value = 'grid';
        vi.clearAllMocks();
    });

    test.each([
        {
            activeButtonName: 'Show products in grid view',
            inactiveButtonName: 'Show products in list view',
            productListViewMode: 'grid' as const,
        },
        {
            activeButtonName: 'Show products in list view',
            inactiveButtonName: 'Show products in grid view',
            productListViewMode: 'list' as const,
        },
    ])('marks the $productListViewMode view as active', ({
        activeButtonName,
        inactiveButtonName,
        productListViewMode,
    }) => {
        productListViewModeState.value = productListViewMode;

        render(<ProductListViewModeToggle />);

        expect(screen.getByRole('button', { name: activeButtonName })).toHaveAttribute('aria-pressed', 'true');
        expect(screen.getByRole('button', { name: inactiveButtonName })).toHaveAttribute('aria-pressed', 'false');
    });

    test.each([
        { buttonName: 'Show products in grid view', productListViewMode: 'grid' as const },
        { buttonName: 'Show products in list view', productListViewMode: 'list' as const },
    ])('updates the view mode to $productListViewMode', async ({ buttonName, productListViewMode }) => {
        const user = userEvent.setup();
        render(<ProductListViewModeToggle />);

        await user.click(screen.getByRole('button', { name: buttonName }));

        expect(setCookiesStoreStateMock).toHaveBeenCalledWith({ productListViewMode });
    });

    test('supports keyboard activation', async () => {
        const user = userEvent.setup();
        render(<ProductListViewModeToggle />);

        await user.tab();
        await user.tab();

        expect(screen.getByRole('button', { name: 'Show products in list view' })).toHaveFocus();

        await user.keyboard('{Enter}');

        expect(setCookiesStoreStateMock).toHaveBeenCalledWith({ productListViewMode: 'list' });
    });
});
