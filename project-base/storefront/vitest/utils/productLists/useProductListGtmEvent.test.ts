import { act, renderHook, waitFor } from '@testing-library/react';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { ProductInterfaceType } from 'types/product';
import { useProductListGtmEvent } from 'utils/productLists/useProductListGtmEvent';
import { describe, expect, test, vi } from 'vitest';

const { domainConfigMock, onGtmChangeProductListItemEventHandlerMock } = vi.hoisted(() => ({
    domainConfigMock: { url: 'https://example.com' },
    onGtmChangeProductListItemEventHandlerMock: vi.fn(),
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canSeePrices: true }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => domainConfigMock,
}));

vi.mock('gtm/handlers/onGtmChangeProductListItemEventHandler', () => ({
    onGtmChangeProductListItemEventHandler: onGtmChangeProductListItemEventHandlerMock,
}));

describe('useProductListGtmEvent', () => {
    test('should keep pending GTM context when repeated toggle is ignored', async () => {
        const product = { uuid: 'product-uuid' } as ProductInterfaceType;
        const toggleProductInListMock = vi.fn().mockReturnValueOnce(true).mockReturnValueOnce(false);
        const { result } = renderHook(() =>
            useProductListGtmEvent(GtmEventType.add_to_wishlist, GtmEventType.remove_from_wishlist),
        );

        act(() => {
            result.current.toggleProductInListWithGtm(
                product,
                GtmProductListNameType.category_detail,
                3,
                toggleProductInListMock,
            );
            result.current.toggleProductInListWithGtm(
                product,
                GtmProductListNameType.category_detail,
                3,
                toggleProductInListMock,
            );
            result.current.pushAddProductListGtmEvent(product.uuid);
        });

        await waitFor(() => expect(onGtmChangeProductListItemEventHandlerMock).toHaveBeenCalledTimes(1));
        expect(onGtmChangeProductListItemEventHandlerMock).toHaveBeenCalledWith(
            GtmEventType.add_to_wishlist,
            product,
            domainConfigMock,
            3,
            GtmProductListNameType.category_detail,
            false,
        );
    });
});
