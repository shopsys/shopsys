import { parse } from 'graphql';
import { LoginMutationDocument } from 'graphql/requests/auth/mutations/LoginMutation.generated';
import { CombinedError, Operation } from 'urql';
import { getErrorExchange } from 'urql/errorExchange';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { fromValue, toPromise } from 'wonka';

const {
    clearAuthCookiesMock,
    logExceptionMock,
    removeAccessTokenFromCookiesMock,
    removeTokensFromCookiesMock,
    showErrorMessageMock,
    errorDebugging,
} = vi.hoisted(() => ({
    clearAuthCookiesMock: vi.fn().mockResolvedValue(undefined),
    logExceptionMock: vi.fn(),
    removeAccessTokenFromCookiesMock: vi.fn(),
    removeTokensFromCookiesMock: vi.fn(),
    showErrorMessageMock: vi.fn(),
    errorDebugging: { enabled: false },
}));

vi.mock('utils/errors/isWithErrorDebugging', () => ({
    get isWithErrorDebugging() {
        return errorDebugging.enabled;
    },
    isWithToastAndConsoleErrorDebugging: false,
}));

vi.mock('utils/auth/authMutationFetcher', () => ({
    clearAuthCookies: clearAuthCookiesMock,
}));

vi.mock('utils/auth/removeTokensFromCookies', () => ({
    removeAccessTokenFromCookies: removeAccessTokenFromCookiesMock,
    removeTokensFromCookies: removeTokensFromCookiesMock,
}));

vi.mock('utils/errors/logException', () => ({
    logException: logExceptionMock,
}));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: showErrorMessageMock,
}));

describe('getErrorExchange', () => {
    beforeEach(() => {
        errorDebugging.enabled = false;
    });

    test('should clear auth cookies for graphql auth errors returned with HTTP 200', async () => {
        const domainConfig = {
            url: 'http://example.com',
        };
        const operation = {
            key: 1,
            kind: 'query',
            query: parse('query TestQuery { __typename }'),
            variables: {},
            context: {},
        } as Operation;
        const error = new CombinedError({
            response: new Response(null, { status: 200 }),
            graphQLErrors: [
                {
                    message: 'Token is expired. Please renew.',
                    extensions: { userCode: 'expired-token' },
                },
            ],
        });
        const exchange = getErrorExchange(
            ((value: string) => value) as never,
            domainConfig as never,
        )({
            forward: () =>
                fromValue({
                    operation,
                    error,
                } as never),
        } as never);

        clearAuthCookiesMock.mockClear();
        removeAccessTokenFromCookiesMock.mockClear();
        removeTokensFromCookiesMock.mockClear();

        await toPromise(exchange(fromValue(operation)));

        expect(removeAccessTokenFromCookiesMock).toHaveBeenCalledWith(domainConfig);
        expect(clearAuthCookiesMock).toHaveBeenCalledWith(domainConfig);
        expect(removeTokensFromCookiesMock).not.toHaveBeenCalled();
    });

    test('should not clear auth cookies for non-auth graphql errors', async () => {
        const domainConfig = {
            url: 'http://example.com',
        };
        const operation = {
            key: 1,
            kind: 'query',
            query: parse('query TestQuery { __typename }'),
            variables: {},
            context: {},
        } as Operation;
        const error = new CombinedError({
            response: new Response(null, { status: 200 }),
            graphQLErrors: [
                {
                    message: 'Category not found.',
                    extensions: { userCode: 'category-not-found' },
                },
            ],
        });
        const exchange = getErrorExchange(
            ((value: string) => value) as never,
            domainConfig as never,
        )({
            forward: () =>
                fromValue({
                    operation,
                    error,
                } as never),
        } as never);

        clearAuthCookiesMock.mockClear();
        removeAccessTokenFromCookiesMock.mockClear();
        removeTokensFromCookiesMock.mockClear();

        await toPromise(exchange(fromValue(operation)));

        expect(removeTokensFromCookiesMock).not.toHaveBeenCalled();
        expect(removeAccessTokenFromCookiesMock).not.toHaveBeenCalled();
        expect(clearAuthCookiesMock).not.toHaveBeenCalled();
    });

    test('should show the specific application error for a configured mutation', async () => {
        const domainConfig = {
            url: 'http://example.com',
        };
        const operation = {
            key: 1,
            kind: 'mutation',
            query: parse('mutation ApplyCodeToCartMutation { __typename }'),
            variables: {},
            context: {},
        } as Operation;
        const error = new CombinedError({
            response: new Response(null, { status: 200 }),
            graphQLErrors: [
                {
                    message: 'Too many attempts to apply a code. Try again later.',
                    extensions: { userCode: 'too-many-code-application-attempts' },
                },
            ],
        });
        const exchange = getErrorExchange(
            ((value: string) => value) as never,
            domainConfig as never,
        )({
            forward: () =>
                fromValue({
                    operation,
                    error,
                } as never),
        } as never);

        showErrorMessageMock.mockClear();

        await toPromise(exchange(fromValue(operation)));

        expect(showErrorMessageMock).toHaveBeenCalledWith(
            'Too many attempts to apply a code. Try again later.',
            'cart',
            { errorType: 'too-many-code-application-attempts' },
        );
    });

    test.each([
        { kind: 'query' as const, debugging: false, location: 'handleErrorMessagesForUsers' },
        { kind: 'query' as const, debugging: true, location: 'handleErrorMessagesForDevelopment' },
        { kind: 'mutation' as const, debugging: false, location: 'handleErrorMessagesForMutation.TestMutation' },
    ])('preserves GraphQL exceptions for $kind with debugging=$debugging', async ({ kind, debugging, location }) => {
        errorDebugging.enabled = debugging;
        const operation = {
            key: 1,
            kind,
            query: parse(kind === 'query' ? 'query TestQuery { __typename }' : 'mutation TestMutation { __typename }'),
            variables: {},
            context: {},
        } as Operation;
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Privacy policy article is not configured.',
                    path: ['settings', 'privacyPolicyArticleUrl'],
                    extensions: { userCode: 'article-not-found-privacy-policy', code: 404 },
                },
            ],
        });
        const exchange = getErrorExchange(
            ((value: string) => value) as never,
            { url: 'http://example.com' } as never,
        )({ forward: () => fromValue({ operation, error } as never) } as never);

        await toPromise(exchange(fromValue(operation)));

        expect(logExceptionMock.mock.calls[0][0]).toBe(error);
        expect(logExceptionMock).toHaveBeenCalledWith(
            error,
            expect.objectContaining({
                location: `getErrorExchange.${location}`,
            }),
        );
        expect(JSON.parse(logExceptionMock.mock.calls[0][1].graphQLErrors)).toEqual([
            {
                message: 'Privacy policy article is not configured.',
                path: ['settings', 'privacyPolicyArticleUrl'],
                extensions: { userCode: 'article-not-found-privacy-policy', code: 404 },
            },
        ]);
    });

    test('preserves the network error details when a mutation fails', async () => {
        const networkError = new TypeError('Failed to fetch');
        const error = new CombinedError({ networkError });
        const operation = {
            key: 1,
            kind: 'mutation',
            query: parse('mutation AddToCartMutation { __typename }'),
            variables: {},
            context: {},
        } as Operation;
        const exchange = getErrorExchange(
            ((value: string) => value) as never,
            { url: 'http://example.com' } as never,
        )({ forward: () => fromValue({ operation, error } as never) } as never);

        await toPromise(exchange(fromValue(operation)));

        expect(logExceptionMock.mock.calls[0][0]).toBe(error);
        expect(logExceptionMock).toHaveBeenCalledWith(error, {
            graphQLErrors: '[]',
            networkError,
            location: 'getErrorExchange.handleErrorMessagesForMutation.AddToCartMutation',
        });
    });
    test('returns invalid login credentials to the caller without reporting them to Sentry', async () => {
        const operation = {
            key: 1,
            kind: 'mutation',
            query: LoginMutationDocument,
            variables: {},
            context: {},
        } as Operation;
        const error = new CombinedError({
            response: new Response(null, { status: 200 }),
            graphQLErrors: [
                {
                    message: 'Log in failed.',
                    path: ['Login'],
                    extensions: { userCode: 'invalid-credentials', code: 404 },
                },
            ],
        });
        const exchange = getErrorExchange(
            ((value: string) => value) as never,
            { url: 'http://example.com' } as never,
        )({ forward: () => fromValue({ operation, error } as never) } as never);

        const result = await toPromise(exchange(fromValue(operation)));

        expect(result.error).toBe(error);
        expect(logExceptionMock).not.toHaveBeenCalled();
        expect(removeTokensFromCookiesMock).not.toHaveBeenCalled();
        expect(removeAccessTokenFromCookiesMock).not.toHaveBeenCalled();
        expect(clearAuthCookiesMock).not.toHaveBeenCalled();
    });

    test.each([
        {
            scenario: 'an unexpected error with the same message',
            query: LoginMutationDocument,
            error: new CombinedError({ graphQLErrors: [{ message: 'Log in failed.', extensions: { code: 500 } }] }),
        },
        {
            scenario: 'an unexpected GraphQL error mixed with invalid credentials',
            query: LoginMutationDocument,
            error: new CombinedError({
                graphQLErrors: [
                    { message: 'Internal Server Error', extensions: { code: 500 } },
                    { message: 'Log in failed.', extensions: { userCode: 'invalid-credentials', code: 404 } },
                ],
            }),
        },
        {
            scenario: 'a network error accompanying invalid credentials',
            query: LoginMutationDocument,
            error: new CombinedError({
                networkError: new TypeError('Failed to fetch'),
                graphQLErrors: [
                    { message: 'Log in failed.', extensions: { userCode: 'invalid-credentials', code: 404 } },
                ],
            }),
        },
        {
            scenario: 'an HTTP server error accompanying invalid credentials',
            query: LoginMutationDocument,
            error: new CombinedError({
                response: new Response(null, { status: 500 }),
                graphQLErrors: [
                    { message: 'Log in failed.', extensions: { userCode: 'invalid-credentials', code: 404 } },
                ],
            }),
        },
        {
            scenario: 'invalid credentials from another mutation',
            query: parse('mutation LoginViaExchangeTokenMutation { __typename }'),
            error: new CombinedError({
                graphQLErrors: [
                    { message: 'Invalid exchange token.', extensions: { userCode: 'invalid-credentials', code: 404 } },
                ],
            }),
        },
    ])('still reports $scenario', async ({ query, error }) => {
        const operation = {
            key: 1,
            kind: 'mutation',
            query,
            variables: {},
            context: {},
        } as Operation;
        const exchange = getErrorExchange(
            ((value: string) => value) as never,
            { url: 'http://example.com' } as never,
        )({ forward: () => fromValue({ operation, error } as never) } as never);

        const result = await toPromise(exchange(fromValue(operation)));

        expect(result.error).toBe(error);
        expect(logExceptionMock).toHaveBeenCalledExactlyOnceWith(error, {
            graphQLErrors: JSON.stringify(error.graphQLErrors),
            networkError: error.networkError,
            location: expect.stringContaining('getErrorExchange.handleErrorMessagesForMutation.'),
        });
    });
});
