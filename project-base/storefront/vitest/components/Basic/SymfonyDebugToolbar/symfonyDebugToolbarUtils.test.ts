import { renderHook } from '@testing-library/react';
import { useRequests } from 'components/Basic/SymfonyDebugToolbar/symfonyDebugToolbarUtils';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const { mockApply, mockOff, mockOn } = vi.hoisted(() => ({
    mockApply: vi.fn(),
    mockOff: vi.fn(),
    mockOn: vi.fn(),
}));

vi.mock('@mswjs/interceptors', () => ({
    BatchInterceptor: vi.fn().mockImplementation(() => ({
        apply: mockApply,
        off: mockOff,
        on: mockOn,
    })),
}));

vi.mock('@mswjs/interceptors/fetch', () => ({
    FetchInterceptor: vi.fn(),
}));

vi.mock('@mswjs/interceptors/XMLHttpRequest', () => ({
    XMLHttpRequestInterceptor: vi.fn(),
}));

describe('useRequests', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    test('unregisters the response listener on unmount', () => {
        const { unmount } = renderHook(() => useRequests('x-debug-token', 'x-debug-token-link'));

        expect(mockApply).toHaveBeenCalledTimes(1);
        expect(mockOn).toHaveBeenCalledTimes(1);

        const [eventName, listener] = mockOn.mock.calls[0];

        expect(eventName).toBe('response');
        expect(listener).toBeInstanceOf(Function);

        unmount();

        expect(mockOff).toHaveBeenCalledTimes(1);
        expect(mockOff).toHaveBeenCalledWith('response', listener);
    });

    test('replaces the response listener when hook inputs change', () => {
        const { rerender } = renderHook(
            ({ tokenHeader, tokenLinkHeader }) => useRequests(tokenHeader, tokenLinkHeader),
            {
                initialProps: { tokenHeader: 'x-debug-token', tokenLinkHeader: 'x-debug-token-link' },
            },
        );

        const initialListener = mockOn.mock.calls[0][1];

        rerender({ tokenHeader: 'x-next-debug-token', tokenLinkHeader: 'x-debug-token-link' });

        expect(mockOff).toHaveBeenCalledWith('response', initialListener);
        expect(mockOn).toHaveBeenCalledTimes(2);
    });
});
