import { act, render, renderHook, screen } from '@testing-library/react';
import { ToastContainerWrapper } from 'components/Pages/App/ToastContainerWrapper';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { toast } from 'react-toastify';
import { useSessionStore } from 'store/useSessionStore';
import { ProductInterfaceType } from 'types/product';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/Popup/ProductVariantsPopup', () => ({ ProductVariantsPopup: () => null }));

type ProductListCallbacks = {
    addProductSuccess: (updatedProductList: undefined, productUuid: string) => void;
    removeProductSuccess: (updatedProductList: undefined, productUuid: string) => void;
    removeProductError: (productUuid: string) => void;
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

describe('useComparison', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        productListCallbacksRef.current = undefined;
        inList.clear();
        toggleProductInListWithGtmMock.mockClear();
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

    test('notifies about removals only after success, keeping the previous notification on failure', () => {
        const onProductRemoved = vi.fn();
        const { result } = renderHook(() => useComparison({ onProductRemoved }));

        act(() => productListCallbacksRef.current?.removeProductSuccess(undefined, 'first-product'));
        expect(onProductRemoved).toHaveBeenLastCalledWith('first-product');

        act(() => {
            result.current.toggleProductInComparison(
                { uuid: 'second-product' } as ProductInterfaceType,
                GtmProductListNameType.product_comparison_page,
            );
        });
        expect(onProductRemoved).toHaveBeenCalledTimes(1);

        act(() => productListCallbacksRef.current?.removeProductError('second-product'));
        expect(onProductRemoved).toHaveBeenCalledTimes(1);

        act(() => productListCallbacksRef.current?.removeProductSuccess(undefined, 'second-product'));
        expect(onProductRemoved).toHaveBeenCalledTimes(2);
        expect(onProductRemoved).toHaveBeenLastCalledWith('second-product');
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
    test('opens a selector for a product group and migrates it only after a confirmed variant selection', () => {
        const group = {
            __typename: 'MainVariant',
            uuid: 'family',
            slug: '/family',
            fullName: 'Family',
        } as ProductInterfaceType;
        const { result } = renderHook(() => useComparison());

        act(() => result.current.toggleProductInComparison(group, GtmProductListNameType.product_detail));

        expect(toggleProductInListWithGtmMock).not.toHaveBeenCalled();
        expect(useSessionStore.getState().portalContent?.props).toEqual(
            expect.objectContaining({ productUuid: 'family', onVariantCompared: expect.any(Function) }),
        );

        inList.add('family');
        act(() => result.current.chooseVariantForComparison(group, GtmProductListNameType.product_comparison_page, 2));
        expect(toggleProductInListWithGtmMock).not.toHaveBeenCalled();
        const selection = useSessionStore.getState().portalContent!.props as { onVariantCompared: () => void };
        act(() => selection.onVariantCompared());
        expect(toggleProductInListWithGtmMock).toHaveBeenCalledWith(
            group,
            GtmProductListNameType.product_comparison_page,
            2,
            expect.any(Function),
        );
    });

    test('allows an existing group to be removed without opening a selector', () => {
        const group = { __typename: 'MainVariant', uuid: 'family' } as ProductInterfaceType;
        inList.add('family');
        const { result } = renderHook(() => useComparison());

        act(() => result.current.toggleProductInComparison(group, GtmProductListNameType.product_comparison_page));

        expect(toggleProductInListWithGtmMock).toHaveBeenCalledWith(
            group,
            GtmProductListNameType.product_comparison_page,
            undefined,
            expect.any(Function),
        );
        expect(useSessionStore.getState().portalContent).toBeNull();
    });
});
