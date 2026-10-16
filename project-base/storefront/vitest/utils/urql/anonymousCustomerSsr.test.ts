import { CurrentCustomerUserAuthQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import type { GetServerSidePropsContext } from 'next';
import type { Translate } from 'next-translate';
import { Client, type ClientOptions, type Exchange, fetchExchange, gql, ssrExchange } from 'urql';
import { createClient } from 'urql/createClient';
import { getCurrentCustomerUserRoles } from 'utils/auth/getCurrentCustomerUserRoles';
import { isUserLoggedInSSR } from 'utils/auth/isUserLoggedInSSR';
import { buildServerSideProps } from 'utils/serverSide/buildServerSideProps';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestConfig } from 'vitest/helpers/mockPublicConfig';

const { fetchMock } = vi.hoisted(() => ({ fetchMock: vi.fn<typeof fetch>() }));

vi.mock('utils/isClient', () => ({ isClient: false }));
vi.mock('@urql/devtools', () => ({ devtoolsExchange: (({ forward }) => forward) satisfies Exchange }));
// Avoid next-urql's browser singleton in jsdom while exercising real exchanges and SSR data.
vi.mock('next-urql', () => ({ initUrqlClient: (options: ClientOptions) => new Client(options) }));
vi.mock('urql/fetcher', () => ({ fetcher: () => fetchMock }));
vi.mock('utils/staticUrls/getServerSideInternationalizedStaticUrl', () => ({
    getServerSideInternationalizedStaticUrl: () => ({ trimmedUrlWithoutQueryParams: '/customer/orders' }),
}));

const response = (data: unknown) =>
    new Response(JSON.stringify({ data }), { headers: { 'Content-Type': 'application/json' } });

const customer = {
    __typename: 'CurrentRegularCustomerUser',
    uuid: 'customer-uuid',
    roles: [TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage],
};

const createContext = (cookie = '') => {
    const headers = new Map<string, string | string[]>();
    return {
        req: { headers: { cookie } },
        res: {
            getHeader: (name: string) => headers.get(name),
            setHeader: (name: string, value: string | string[]) => headers.set(name, value),
        },
        resolvedUrl: '/customer/orders',
        locale: 'en',
        defaultLocale: 'en',
    } as unknown as GetServerSidePropsContext;
};

const createServer = (cookie = '', domain = defaultTestConfig.domains[0], withContext = true) => {
    const cache = ssrExchange({ isClient: false });
    const context = createContext(cookie);
    const domainConfig = { ...domain, publicGraphqlEndpoint: 'https://example.com/graphql/' };
    const client = createClient({
        t: ((key: string) => key) as Translate,
        ssrExchange: cache,
        domainConfig,
        context: withContext ? context : undefined,
    });
    return { client, cache, context, domainConfig };
};

beforeEach(() => {
    // cookies-next detects the server via window, independently of our isClient helper.
    vi.stubGlobal('window', undefined);
    fetchMock.mockReset();
    fetchMock.mockImplementation(async () => response({ currentCustomerUser: customer }));
});

afterEach(() => vi.unstubAllGlobals());

describe.each(defaultTestConfig.domains)('anonymous SSR on domain $domainId ($type, $defaultLocale)', (domain) => {
    test.each([
        ['auth', CurrentCustomerUserAuthQueryDocument],
        ['full', CurrentCustomerUserQueryDocument],
    ] as const)('keeps explicit guest data and roles without an HTTP request in %s mode', async (_, query) => {
        const { client, cache } = createServer('', domain);

        const result = await client.query(query, {}).toPromise();

        expect(result.data).toEqual({ currentCustomerUser: null });
        expect(client.readQuery(query, {})?.data).toEqual({ currentCustomerUser: null });
        expect(getCurrentCustomerUserRoles(client)).toEqual(Object.values(TypeCustomerUserRoleEnum));
        expect(isUserLoggedInSSR(client)).toBe(false);
        expect(fetchMock).not.toHaveBeenCalled();

        const browser = new Client({
            url: 'https://example.com/graphql/',
            exchanges: [ssrExchange({ isClient: true, initialState: cache.extractData() }), fetchExchange],
            fetch: fetchMock,
        });
        expect((await browser.query(query, {}).toPromise()).data).toEqual({ currentCustomerUser: null });
        expect(fetchMock).not.toHaveBeenCalled();
    });

    test('still sends an access token to the API for validation', async () => {
        const { client } = createServer(`accessToken-${domain.domainId}=unverified-token`, domain);

        const result = await client.query(CurrentCustomerUserAuthQueryDocument, {}).toPromise();

        expect(result.data?.currentCustomerUser).toEqual(customer);
        expect(getCurrentCustomerUserRoles(client)).toEqual(customer.roles);
        expect(isUserLoggedInSSR(client)).toBe(true);
        expect(fetchMock).toHaveBeenCalledTimes(1);
        expect(new Headers(fetchMock.mock.calls[0][1]?.headers).get('X-Auth-Token')).toBe('Bearer unverified-token');
    });

    test('refreshes a refresh-only session before loading the customer', async () => {
        fetchMock.mockImplementation(async (_, init) => {
            const body = JSON.parse(init?.body as string);
            if (body.operationName === 'RefreshTokens') {
                expect(body.variables.refreshToken).toBe('refresh-token');
                return response({
                    RefreshTokens: { accessToken: 'new-access-token', refreshToken: 'new-refresh-token' },
                });
            }
            expect(new Headers(init?.headers).get('X-Auth-Token')).toBe('Bearer new-access-token');
            return response({ currentCustomerUser: customer });
        });
        const { client } = createServer(`refreshToken-${domain.domainId}=refresh-token`, domain);

        const result = await client.query(CurrentCustomerUserAuthQueryDocument, {}).toPromise();

        expect(result.data?.currentCustomerUser).toEqual(customer);
        expect(fetchMock).toHaveBeenCalledTimes(2);
        expect(isUserLoggedInSSR(client)).toBe(true);
    });
});

test('does not reuse credentials from a different domain', async () => {
    const { client } = createServer('accessToken-2=other-token; refreshToken-2=other-refresh');

    expect((await client.query(CurrentCustomerUserAuthQueryDocument, {}).toPromise()).data).toEqual({
        currentCustomerUser: null,
    });
    expect(fetchMock).not.toHaveBeenCalled();
});

test('does not infer a guest without server request context', async () => {
    const { client } = createServer('', defaultTestConfig.domains[0], false);

    expect(
        (await client.query(CurrentCustomerUserAuthQueryDocument, {}).toPromise()).data?.currentCustomerUser,
    ).toEqual(customer);
    expect(fetchMock).toHaveBeenCalledTimes(1);
});

test('still fetches unrelated queries for an anonymous visitor', async () => {
    const { client } = createServer();
    fetchMock.mockResolvedValue(response({ __typename: 'Query' }));

    expect((await client.query(gql`query OtherQuery { __typename }`, {}).toPromise()).data).toEqual({
        __typename: 'Query',
    });
    expect(fetchMock).toHaveBeenCalledTimes(1);
});

test('redirects anonymous visitors away from a protected page', async () => {
    const { client, cache, context, domainConfig } = createServer();

    const result = await buildServerSideProps({
        client,
        ssrExchange: cache,
        redisClient: undefined,
        context,
        domainConfig,
        layoutResult: { resolvedQueries: [], seoPageSlug: null },
        authenticationConfig: { authenticationRequired: true },
    });

    expect(result).toMatchObject({ redirect: { statusCode: 302, destination: expect.stringContaining('/login') } });
    expect(fetchMock).not.toHaveBeenCalled();
});
