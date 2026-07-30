import { act, render, renderHook, screen } from '@testing-library/react';
import { ToastContainerWrapper } from 'components/Pages/App/ToastContainerWrapper';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { toast } from 'react-toastify';
import { useSessionStore } from 'store/useSessionStore';
import { ProductInterfaceType } from 'types/product';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

type ProductListCallbacks = {
    addProductSuccess: (updatedProductList: undefined, productUuid: string) => void;
};

const { productListCallbacksRef, toggleProductInListWithGtmMock } = vi.hoisted(() => ({
    productListCallbacksRef: { current: undefined as ProductListCallbacks | undefined },
    toggleProductInListWithGtmMock: vi.fn(),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/productLists/useProductList', () => ({
    useProductList: (_productListType: unknown, callbacks: ProductListCallbacks) => {
        productListCallbacksRef.current = callbacks;

        return {
            isProductInList: vi.fn(),
            isProductListFetching: false,
            productListData: undefined,
            removeList: vi.fn(),
            toggleProductInList: vi.fn(),
        };
    },
}));

vi.mock('utils/productLists/useProductListGtmEvent', () => ({
    useProductListGtmEvent: () => ({
        clearProductListGtmContext: vi.fn(),
        pushAddProductListGtmEvent: vi.fn(),
        pushRemoveProductListGtmEvent: vi.fn(),
        toggleProductInListWithGtm: toggleProductInListWithGtmMock,
    }),
}));

vi.mock('utils/productLists/useUpdateProductListUuid', () => ({
    useUpdateProductListUuid: () => vi.fn(),
}));

describe('useComparison', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        productListCallbacksRef.current = undefined;
        toggleProductInListWithGtmMock.mockClear();
        useSessionStore.setState({ storedFocusElement: null });
    });

    afterEach(() => {
        act(() => {
            toast.dismiss();
            vi.runOnlyPendingTimers();
        });
        vi.useRealTimers();
        useSessionStore.setState({ storedFocusElement: null });
    });

    test('does not restore stale focus after the comparison toast closes', () => {
        render(
            <>
                <button type="button">Compare</button>
                <button type="button">Wishlist</button>
                <ToastContainerWrapper />
            </>,
        );
        const comparisonButton = screen.getByRole('button', { name: 'Compare' });
        comparisonButton.focus();
        useSessionStore.setState({ storedFocusElement: comparisonButton });
        const { result } = renderHook(() => useComparison());

        act(() => {
            result.current.toggleProductInComparison(
                { uuid: 'product-uuid' } as ProductInterfaceType,
                GtmProductListNameType.product_detail,
            );
            productListCallbacksRef.current?.addProductSuccess(undefined, 'product-uuid');
        });
        expect(screen.getByText('Product added to comparison.')).toBeInTheDocument();

        const wishlistButton = screen.getByRole('button', { name: 'Wishlist' });
        wishlistButton.focus();

        act(() => {
            vi.advanceTimersByTime(6_500);
        });

        expect(document.activeElement).toBe(wishlistButton);
    });
});
