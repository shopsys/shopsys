import { parse } from 'graphql';
import { CombinedError, Operation } from 'urql';
import { getErrorExchange } from 'urql/errorExchange';
import { describe, expect, test, vi } from 'vitest';
import { fromValue, toPromise } from 'wonka';

const { removeTokensFromCookiesMock, logExceptionMock, showErrorMessageMock } = vi.hoisted(() => ({
    removeTokensFromCookiesMock: vi.fn(),
    logExceptionMock: vi.fn(),
    showErrorMessageMock: vi.fn(),
}));

vi.mock('utils/auth/removeTokensFromCookies', () => ({
    removeTokensFromCookies: removeTokensFromCookiesMock,
}));

vi.mock('utils/errors/logException', () => ({
    logException: logExceptionMock,
}));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: showErrorMessageMock,
}));

describe('getErrorExchange', () => {
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

        removeTokensFromCookiesMock.mockClear();

        await toPromise(exchange(fromValue(operation)));

        expect(removeTokensFromCookiesMock).toHaveBeenCalledWith(domainConfig, undefined);
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

        removeTokensFromCookiesMock.mockClear();

        await toPromise(exchange(fromValue(operation)));

        expect(removeTokensFromCookiesMock).not.toHaveBeenCalled();
    });
});
