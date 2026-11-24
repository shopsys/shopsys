import { act, render, renderHook, screen } from '@testing-library/react';
import { ToastContainerWrapper } from 'components/Pages/App/ToastContainerWrapper';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { toast } from 'react-toastify';
import { useSessionStore } from 'store/useSessionStore';
import { ProductInterfaceType } from 'types/product';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Popup/ProductVariantsPopup', () => ({ ProductVariantsPopup: () => null }));

type ProductListCallbacks = {
    addProductSuccess: (productUuid: string) => void;
};

const { productListCallbacksRef, toggleProductInListWithGtmMock, inList } = vi.hoisted(() => ({
    productListCallbacksRef: { current: undefined as ProductListCallbacks | undefined },
    toggleProductInListWithGtmMock: vi.fn(),
    inList: new Set<string>(),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/productLists/useProductList', () => ({
    useProductList: (_productListType: unknown, callbacks: ProductListCallbacks) => {
        productListCallbacksRef.current = callbacks;

        return {
            isProductInList: (uuid: string) => inList.has(uuid),
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
        inList.clear();
        useSessionStore.setState({ storedFocusElement: null, portalContent: null });
    });

    afterEach(() => {
        act(() => {
            toast.dismiss();
            vi.runOnlyPendingTimers();
        });
        vi.useRealTimers();
        useSessionStore.setState({ storedFocusElement: null, portalContent: null });
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
            productListCallbacksRef.current?.addProductSuccess('product-uuid');
        });
        expect(screen.getByText('The item has been added to your wishlist.')).toBeInTheDocument();

        const comparisonButton = screen.getByRole('button', { name: 'Compare' });
        comparisonButton.focus();

        act(() => {
            vi.advanceTimersByTime(6_500);
        });

        expect(document.activeElement).toBe(comparisonButton);
    });

    test('opens the variant selector for a product group instead of saving it', () => {
        const group = {
            __typename: 'MainVariant',
            uuid: 'family',
            slug: '/family',
            fullName: 'Family',
        } as ProductInterfaceType;
        const { result } = renderHook(() => useWishlist());

        act(() => result.current.toggleProductInWishlist(group, GtmProductListNameType.category_detail, 3));

        expect(toggleProductInListWithGtmMock).not.toHaveBeenCalled();
        expect(useSessionStore.getState().portalContent?.props).toEqual(
            expect.objectContaining({
                productUuid: 'family',
                productSlug: '/family',
                productName: 'Family',
                gtmProductListName: GtmProductListNameType.category_detail,
                listIndex: 3,
            }),
        );
    });

    test('removes a product group saved earlier without opening the selector', () => {
        const group = { __typename: 'MainVariant', uuid: 'family' } as ProductInterfaceType;
        inList.add('family');
        const { result } = renderHook(() => useWishlist());

        act(() => result.current.toggleProductInWishlist(group, GtmProductListNameType.wishlist));

        expect(toggleProductInListWithGtmMock).toHaveBeenCalledWith(
            group,
            GtmProductListNameType.wishlist,
            undefined,
            expect.any(Function),
        );
        expect(useSessionStore.getState().portalContent).toBeNull();
    });
});
