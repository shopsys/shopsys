import { RedisClientType } from 'redis';
import { Mock, afterEach, describe, expect, test, vi } from 'vitest';
import { cleanup, render, waitFor } from '@testing-library/react';
import { createClient } from 'urql/createClient';
import { Provider, ssrExchange, useQuery } from 'urql';
import gql from 'graphql-tag';

const mockRequestWithFetcher = vi.fn(async () => undefined);

vi.mock('urql/fetcher', () => ({
    fetcher: vi.fn(() => mockRequestWithFetcher),
}));

const isClientGetter = vi.fn();
vi.mock('helpers/isClient', () => ({
    get isClient() {
        return isClientGetter();
    },
}));

vi.mock('next/config', () => ({
    default: () => ({ serverRuntimeConfig: { internalGraphqlEndpoint: TEST_URL } }),
}));

const mockRedisClientGet: Mock<[], null | string> = vi.fn(() => null);
const mockRedisClient = {
    get: mockRedisClientGet,
    set: vi.fn(() => null),
} as unknown as RedisClientType;

const TEST_URL = 'https://test.ts/graphql/';
const QUERY_OBJECT = gql`
    query NotificationBars @redisCache(ttl: 3600) {
        notificationBars {
            text
        }
    }
`;
const REQUEST_BODY =
    '{"operationName":"NotificationBars","query":"query NotificationBars @redisCache(ttl: 3600) {\\n  notificationBars {\\n    text\\n    __typename\\n  }\\n}","variables":{}}';

describe('createClient test', () => {
    afterEach(cleanup);

    test('created client (and URQL) do not filter out Redis cache directive on the client (in component)', async () => {
        (isClientGetter as Mock).mockImplementation(() => true);

        const UrqlWrapper: FC = ({ children }) => {
            const publicGraphqlEndpoint = TEST_URL;

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
            expect(mockRequestWithFetcher).toBeCalledWith(TEST_URL, expect.objectContaining({ body: REQUEST_BODY }));
        });
    });

    test('created client (and URQL) do not filter out Redis cache directive on the server', async () => {
        (isClientGetter as Mock).mockImplementation(() => false);

        const client = createClient({
            t: () => 'foo' as any,
            ssrExchange: ssrExchange(),
            publicGraphqlEndpoint: TEST_URL,
            redisClient: mockRedisClient,
        });

        await client.query(QUERY_OBJECT, undefined).toPromise();

        expect(mockRequestWithFetcher).toBeCalledWith(TEST_URL, expect.objectContaining({ body: REQUEST_BODY }));
    });
});
