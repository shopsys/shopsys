import { act, render, renderHook, screen } from '@testing-library/react';
import { ToastContainerWrapper } from 'components/Pages/App/ToastContainerWrapper';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { toast } from 'react-toastify';
import { useSessionStore } from 'store/useSessionStore';
import { ProductInterfaceType } from 'types/product';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
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

describe('useWishlist', () => {
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

    test('does not restore stale focus after the wishlist toast closes', () => {
        render(
            <>
                <button type="button">Wishlist</button>
                <button type="button">Compare</button>
                <ToastContainerWrapper />
            </>,
        );
        const wishlistButton = screen.getByRole('button', { name: 'Wishlist' });
        wishlistButton.focus();
        useSessionStore.setState({ storedFocusElement: wishlistButton });
        const { result } = renderHook(() => useWishlist());

        act(() => {
            result.current.toggleProductInWishlist(
                { uuid: 'product-uuid' } as ProductInterfaceType,
                GtmProductListNameType.product_detail,
            );
            productListCallbacksRef.current?.addProductSuccess(undefined, 'product-uuid');
        });
        expect(screen.getByText('The item has been added to your wishlist.')).toBeInTheDocument();

        const comparisonButton = screen.getByRole('button', { name: 'Compare' });
        comparisonButton.focus();

        act(() => {
            vi.advanceTimersByTime(6_500);
        });

        expect(document.activeElement).toBe(comparisonButton);
    });
});
