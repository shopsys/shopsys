import { cleanup, render, waitFor } from '@testing-library/react';
import gql from 'graphql-tag';
import { RedisClientType } from 'redis';
import { Provider, ssrExchange, useQuery } from 'urql';
import { createClient } from 'urql/createClient';
import { Mock, afterEach, describe, expect, test, vi } from 'vitest';

const mockRequestWithFetcher = vi.fn(async () => undefined);

vi.mock('urql/fetcher', () => ({
    fetcher: vi.fn(() => mockRequestWithFetcher),
}));

vi.mock('next/config', () => ({
    default: () => ({
        serverRuntimeConfig: { internalGraphqlEndpoint: 'https://test.ts/graphql/' },
        publicRuntimeConfig: {
            errorDebuggingLevel: 'no-debug',
            domains: [{ url: 'https://test.ts/' }, { url: 'https://test.ts/' }],
        },
    }),
}));

const mockRedisClientGet: Mock<[], null | string> = vi.fn(() => null);
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
const REQUEST_BODY =
    '{"operationName":"NotificationBars","query":"query NotificationBars @redisCache(ttl: 3600) {\\n  notificationBars {\\n    text\\n    __typename\\n  }\\n}","variables":{}}';

describe('createClient test', () => {
    afterEach(cleanup);

    test('created client (and URQL) do not filter out Redis cache directive on the client (in component)', async () => {
        const publicGraphqlEndpoint = 'https://test.ts/graphql/';

        const UrqlWrapper: FC = ({ children }) => {
            return (
                <Provider
                    value={createClient({
                        t: () => 'foo' as any,
                        ssrExchange: ssrExchange(),
                        publicGraphqlEndpoint,
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
                publicGraphqlEndpoint + OPERATION_NAME,
                expect.objectContaining({ body: REQUEST_BODY }),
            );
        });
    });

    test('created client (and URQL) do not filter out Redis cache directive on the server', async () => {
        const publicGraphqlEndpoint = 'https://test.ts/graphql/';

        const client = createClient({
            t: () => 'foo' as any,
            ssrExchange: ssrExchange(),
            publicGraphqlEndpoint,
            redisClient: mockRedisClient,
        });

        await client.query(QUERY_OBJECT, undefined).toPromise();

        expect(mockRequestWithFetcher).toBeCalledWith(
            publicGraphqlEndpoint + OPERATION_NAME,
            expect.objectContaining({ body: REQUEST_BODY }),
        );
    });
});
