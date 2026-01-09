import { cleanup, render, waitFor } from '@testing-library/react';
import gql from 'graphql-tag';
import { RedisClientType } from 'redis';
import { Provider, ssrExchange, useQuery } from 'urql';
import { createClient } from 'urql/createClient';
import { afterEach, describe, expect, test, vi } from 'vitest';

const mockRequestWithFetcher = vi.fn(async () => undefined);

vi.mock('urql/fetcher', () => ({
    fetcher: vi.fn(() => mockRequestWithFetcher),
}));

vi.mock('next/config', () => ({
    default: () => ({
        serverRuntimeConfig: { internalGraphqlEndpoint: 'https://test.ts/' },
        publicRuntimeConfig: {
            errorDebuggingLevel: 'no-debug',
            domains: [{ url: 'https://test.ts/' }, { url: 'https://test.ts/' }],
        },
    }),
}));

const mockRedisClientGet = vi.fn((): string | null => null);
const mockRedisClient = {
    get: mockRedisClientGet,
    set: vi.fn(() => null),
} as unknown as RedisClientType;

const QUERY_OBJECT = gql`
    query NotificationBars @redisCache(ttl: 3600) {
        notificationBars {
            text
        }
    }
`;
const OPERATION_NAME = 'NotificationBars';
describe('createClient test', () => {
    afterEach(cleanup);

    test('created client (and URQL) do not filter out Redis cache directive on the client (in component)', async () => {
        const mockDomainConfig = {
            publicGraphqlEndpoint: 'https://test.ts/graphql/',
            defaultLocale: 'en',
            url: 'https://test.ts',
            currencyCode: 'USD',
            fallbackTimezone: 'UTC',
            domainId: 1,
            mapSetting: { latitude: 0, longitude: 0, zoom: 10 },
            isLuigisBoxActive: false,
            type: 'b2c' as any,
        };

        const UrqlWrapper: FC = ({ children }) => {
            return (
                <Provider
                    value={createClient({
                        t: () => 'foo' as any,
                        ssrExchange: ssrExchange(),
                        domainConfig: mockDomainConfig,
                        redisClient: mockRedisClient,
                    })}
                >
                    {children}
                </Provider>
            );
        };

        const InnerComponentWithUrqlClient: FC = () => {
            useQuery({
                query: QUERY_OBJECT,
            });

            return null;
        };

        render(
            <UrqlWrapper>
                <InnerComponentWithUrqlClient />
            </UrqlWrapper>,
        );

        await waitFor(() => {
            expect(mockRequestWithFetcher).toBeCalledWith(
                expect.stringContaining(mockDomainConfig.publicGraphqlEndpoint + OPERATION_NAME),
                expect.objectContaining({
                    method: 'POST',
                    headers: expect.objectContaining({
                        originalhost: 'test.ts',
                        'x-forwarded-proto': 'on',
                    }),
                }),
            );
        });
    });

    test('created client (and URQL) do not filter out Redis cache directive on the server', async () => {
        const mockDomainConfig = {
            publicGraphqlEndpoint: 'https://test.ts/graphql/',
            defaultLocale: 'en',
            url: 'https://test.ts',
            currencyCode: 'USD',
            fallbackTimezone: 'UTC',
            domainId: 1,
            mapSetting: { latitude: 0, longitude: 0, zoom: 10 },
            isLuigisBoxActive: false,
            type: 'b2c' as any,
        };

        const client = createClient({
            t: () => 'foo' as any,
            ssrExchange: ssrExchange(),
            domainConfig: mockDomainConfig,
            redisClient: mockRedisClient,
        });

        await client.query(QUERY_OBJECT, undefined).toPromise();

        expect(mockRequestWithFetcher).toBeCalledWith(
            expect.stringContaining(mockDomainConfig.publicGraphqlEndpoint + OPERATION_NAME),
            expect.objectContaining({
                method: 'POST',
                headers: expect.objectContaining({
                    originalhost: 'test.ts',
                    'x-forwarded-proto': 'on',
                }),
            }),
        );
    });
});
