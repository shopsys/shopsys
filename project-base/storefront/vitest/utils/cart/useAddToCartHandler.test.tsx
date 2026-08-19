import { act, render, renderHook, screen } from '@testing-library/react';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useSessionStore } from 'store/useSessionStore';
import { useAddToCartHandler } from 'utils/cart/useAddToCartHandler';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const { addToCartMock } = vi.hoisted(() => ({
    addToCartMock: vi.fn(),
}));

vi.mock('next/dynamic', () => ({
    default: () => () => null,
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
});
