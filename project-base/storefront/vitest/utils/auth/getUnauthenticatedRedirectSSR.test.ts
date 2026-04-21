import { getUnauthenticatedRedirectSSR } from 'utils/auth/getUnauthenticatedRedirectSSR';
import { describe, expect, test, vi } from 'vitest';

const { mockGetLoginUrlWithRedirect } = vi.hoisted(() => ({
    mockGetLoginUrlWithRedirect: vi.fn(),
}));

vi.mock('utils/auth/getLoginUrlWithRedirect', () => ({
    getLoginUrlWithRedirect: mockGetLoginUrlWithRedirect,
}));

const createMockContext = () =>
    ({
        resolvedUrl: '/customer/orders',
        res: { statusCode: 200 },
    }) as any;

describe('getUnauthenticatedRedirectSSR', () => {
    test('returns 302 redirect with login URL', () => {
        mockGetLoginUrlWithRedirect.mockReturnValue('/login?r=customer/orders');

        const result = getUnauthenticatedRedirectSSR('/customer/orders', 'https://example.com', createMockContext());

        expect(result).toEqual({
            redirect: {
                statusCode: 302,
                destination: '/login?r=customer/orders',
            },
        });
    });

    test('passes resolved URL and domain URL to getLoginUrlWithRedirect', () => {
        mockGetLoginUrlWithRedirect.mockReturnValue('/login');

        getUnauthenticatedRedirectSSR('/customer/order-detail', 'https://shop.example.com', createMockContext());

        expect(mockGetLoginUrlWithRedirect).toHaveBeenCalledWith(
            '/customer/order-detail',
            'https://shop.example.com',
            expect.any(Object),
        );
    });

    test('returns login without redirect param when URL is empty', () => {
        mockGetLoginUrlWithRedirect.mockReturnValue('/login');

        const result = getUnauthenticatedRedirectSSR('', 'https://example.com', createMockContext());

        expect((result.redirect as any).statusCode).toBe(302);
        expect(result.redirect.destination).toBe('/login');
    });
});
