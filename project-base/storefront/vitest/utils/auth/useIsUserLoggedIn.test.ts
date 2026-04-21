import { renderHook } from '@testing-library/react';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockUseCurrentCustomerUserQueryData = vi.fn();

vi.mock('components/providers/CurrentCustomerUserProvider', () => ({
    useCurrentCustomerUserQueryData: () => mockUseCurrentCustomerUserQueryData(),
}));

describe('useIsUserLoggedIn', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    test('returns true when customer user data is present', () => {
        mockUseCurrentCustomerUserQueryData.mockReturnValue({
            currentCustomerUser: {
                __typename: 'CurrentRegularCustomerUser',
                uuid: 'user-uuid',
                roles: [],
            },
        });

        const { result } = renderHook(() => useIsUserLoggedIn());

        expect(result.current).toBe(true);
    });

    test('returns false when customer user data is undefined', () => {
        mockUseCurrentCustomerUserQueryData.mockReturnValue(undefined);

        const { result } = renderHook(() => useIsUserLoggedIn());

        expect(result.current).toBe(false);
    });

    test('returns false when currentCustomerUser is null', () => {
        mockUseCurrentCustomerUserQueryData.mockReturnValue({
            currentCustomerUser: null,
        });

        const { result } = renderHook(() => useIsUserLoggedIn());

        expect(result.current).toBe(false);
    });
});
