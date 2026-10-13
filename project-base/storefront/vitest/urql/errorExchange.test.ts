import { parse } from 'graphql';
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
});
