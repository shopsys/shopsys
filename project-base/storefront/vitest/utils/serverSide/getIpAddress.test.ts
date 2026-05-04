import { getIpAddress } from 'utils/serverSide/buildServerSideProps';
import { BuildServerSidePropsParams } from 'utils/serverSide/types';
import { describe, expect, test } from 'vitest';

const createContext = (
    xForwardedFor: string | string[] | undefined,
    remoteAddress: string | undefined,
): BuildServerSidePropsParams['context'] =>
    ({
        req: {
            headers: {
                ...(xForwardedFor !== undefined && { 'x-forwarded-for': xForwardedFor }),
            },
            socket: {
                remoteAddress,
            },
        },
    }) as BuildServerSidePropsParams['context'];

describe('getIpAddress', () => {
    test('should use the last valid forwarded IP address', () => {
        expect(getIpAddress(createContext('1.2.3.4, 89.248.244.148', '127.0.0.1'))).toBe('89.248.244.148');
    });

    test('should fallback to remote address when forwarded IP is missing', () => {
        expect(getIpAddress(createContext(undefined, '89.248.244.148'))).toBe('89.248.244.148');
    });

    test('should fallback to remote address when forwarded IP is invalid', () => {
        expect(getIpAddress(createContext('<script>alert(1)</script>', '89.248.244.148'))).toBe('89.248.244.148');
    });

    test('should ignore invalid IP values', () => {
        expect(getIpAddress(createContext('<script>alert(1)</script>', undefined))).toBeUndefined();
    });

    test('should return undefined when request socket is missing', () => {
        expect(
            getIpAddress({
                req: {
                    headers: {},
                },
            } as BuildServerSidePropsParams['context']),
        ).toBeUndefined();
    });
});
