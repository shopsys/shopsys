import { renderHook } from '@testing-library/react';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

const { authorizationState } = vi.hoisted(() => ({
    authorizationState: { canCreateOrder: true },
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => authorizationState,
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => defaultTestDomainConfig,
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: () => null,
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => true,
}));

// the cart query of a logged-in customer is paused or still loading, so no cart is available yet
vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ cart: undefined, promoCodes: [], isCartFetchingOrUnavailable: true, pickupPlace: null }),
}));

describe('useGtmCartInfo', () => {
    test.each([
        { canCreateOrder: false, expectedIsCartLoaded: true },
        { canCreateOrder: true, expectedIsCartLoaded: false },
    ])(
        'reports isCartLoaded $expectedIsCartLoaded for an unavailable cart when canCreateOrder is $canCreateOrder',
        ({ canCreateOrder, expectedIsCartLoaded }) => {
            authorizationState.canCreateOrder = canCreateOrder;

            const { result } = renderHook(() => useGtmCartInfo());

            expect(result.current).toEqual({
                gtmCartInfo: null,
                isCartLoaded: expectedIsCartLoaded,
                pickupPlace: null,
            });
        },
    );
});
