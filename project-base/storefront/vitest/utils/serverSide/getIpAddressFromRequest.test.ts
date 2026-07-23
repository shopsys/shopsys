import { getIpAddressFromRequest } from 'utils/serverSide/getIpAddressFromRequest';
import { describe, expect, test } from 'vitest';

const createRequest = (xForwardedFor: string | string[] | undefined, remoteAddress: string | undefined) => ({
    headers: {
        ...(xForwardedFor !== undefined && { 'x-forwarded-for': xForwardedFor }),
    },
    socket: {
        remoteAddress,
    },
});

describe('getIpAddressFromRequest', () => {
    test('should use the last valid forwarded IP address', () => {
        expect(getIpAddressFromRequest(createRequest('1.2.3.4, 89.248.244.148', '127.0.0.1'))).toBe('89.248.244.148');
    });

    test('should fallback to remote address when forwarded IP is missing', () => {
        expect(getIpAddressFromRequest(createRequest(undefined, '89.248.244.148'))).toBe('89.248.244.148');
    });

    test('should fallback to remote address when forwarded IP is invalid', () => {
        expect(getIpAddressFromRequest(createRequest('<script>alert(1)</script>', '89.248.244.148'))).toBe(
            '89.248.244.148',
        );
    });

    test('should ignore invalid IP values', () => {
        expect(getIpAddressFromRequest(createRequest('<script>alert(1)</script>', undefined))).toBeUndefined();
    });

    test('should return undefined when request socket is missing', () => {
        expect(getIpAddressFromRequest({ headers: {} })).toBeUndefined();
    });
});
