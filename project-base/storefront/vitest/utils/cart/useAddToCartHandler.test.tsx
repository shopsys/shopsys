import { act, render, renderHook, screen } from '@testing-library/react';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useSessionStore } from 'store/useSessionStore';
import { useAddToCartHandler } from 'utils/cart/useAddToCartHandler';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const { addToCartMock } = vi.hoisted(() => ({
    addToCartMock: vi.fn(),
}));

vi.mock('components/Blocks/Popup/AddToCartPopup', () => ({
    AddToCartPopup: () => null,
}));

vi.mock('utils/cart/useAddToCart', () => ({
    useAddToCart: () => ({ addToCart: addToCartMock, isAddingToCart: false }),
}));

describe('useAddToCartHandler', () => {
    beforeEach(() => {
        addToCartMock.mockResolvedValue(null);
        useSessionStore.setState({ portalContent: null, storedFocusElement: null });
    });

    afterEach(() => {
        useSessionStore.setState({ portalContent: null, storedFocusElement: null });
    });

    test('clears the stored focus when adding to cart does not open a popup', async () => {
        render(
            <>
                <button type="button">Previous action</button>
                <button type="button">Add to cart</button>
            </>,
        );
        const previousActionButton = screen.getByRole('button', { name: 'Previous action' });
        const addToCartButton = screen.getByRole('button', { name: 'Add to cart' });
        useSessionStore.setState({ storedFocusElement: previousActionButton });
        addToCartButton.focus();
        const { result } = renderHook(() =>
            useAddToCartHandler({
                gtmMessageOrigin: GtmMessageOriginType.other,
                gtmProductListName: GtmProductListNameType.product_detail,
                isWithSpinbox: false,
                productUuid: 'product-uuid',
                spinboxRef: { current: null },
            }),
        );

        await act(async () => {
            await result.current.onAddToCartHandler();
        });

        expect(useSessionStore.getState().storedFocusElement).toBeNull();
        expect(document.activeElement).toBe(addToCartButton);
    });

    test('stays in the loading state until additional services are persisted', async () => {
        let resolveAddToCart: (value: null) => void = () => undefined;
        const addToCartPromise = new Promise<null>((resolve) => {
            resolveAddToCart = resolve;
        });
        addToCartMock.mockReturnValueOnce(addToCartPromise);
        const onAddToCartFlowStateChange = vi.fn();
        const { result } = renderHook(() =>
            useAddToCartHandler({
                gtmMessageOrigin: GtmMessageOriginType.other,
                gtmProductListName: GtmProductListNameType.product_detail,
                isWithSpinbox: false,
                onAddToCartFlowStateChange,
                productUuid: 'product-uuid',
                spinboxRef: { current: null },
            }),
        );

        let addToCartFlow = Promise.resolve();
        let duplicateAddToCartFlow = Promise.resolve();
        act(() => {
            addToCartFlow = result.current.onAddToCartHandler();
            duplicateAddToCartFlow = result.current.onAddToCartHandler();
        });

        expect(result.current.isAddingToCart).toBe(true);
        expect(addToCartMock).toHaveBeenCalledTimes(1);
        expect(onAddToCartFlowStateChange).toHaveBeenCalledTimes(1);
        expect(onAddToCartFlowStateChange).toHaveBeenCalledWith(true);

        await act(async () => {
            resolveAddToCart(null);
            await Promise.all([addToCartFlow, duplicateAddToCartFlow]);
        });

        expect(result.current.isAddingToCart).toBe(false);
        expect(onAddToCartFlowStateChange).toHaveBeenCalledTimes(2);
        expect(onAddToCartFlowStateChange).toHaveBeenNthCalledWith(2, false);
    });
});
