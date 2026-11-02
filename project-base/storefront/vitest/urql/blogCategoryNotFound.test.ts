import { parse } from 'graphql';
import { GetServerSidePropsContext } from 'next';
import { CombinedError, Operation } from 'urql';
import { getErrorExchange } from 'urql/errorExchange';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { logException } from 'utils/errors/logException';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { describe, expect, test, vi } from 'vitest';
import { fromValue, toPromise } from 'wonka';

const debugging = vi.hoisted(() => ({ enabled: false }));

vi.mock('utils/errors/isWithErrorDebugging', () => ({
    get isWithErrorDebugging() {
        return debugging.enabled;
    },
    isWithToastAndConsoleErrorDebugging: false,
}));
vi.mock('utils/errors/logException', () => ({ logException: vi.fn() }));
vi.mock('utils/toasts/showErrorMessage', () => ({ showErrorMessage: vi.fn() }));

describe('missing blog category', () => {
    test.each([false, true])('returns 404 without logging or toasts with debugging=%s', async (isDebugging) => {
        debugging.enabled = isDebugging;
        const operation = {
            key: 1,
            kind: 'query',
            query: parse('query BlogCategoryQuery { blogCategory(urlSlug: "missing-category") { name } }'),
            variables: {},
            context: {},
        } as Operation;
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'No visible blog category was found by slug "missing-category"',
                    path: ['blogCategory'],
                    extensions: { userCode: 'blog-category-not-found', code: 404 },
                },
            ],
        });
        const result = { operation, error, data: null };
        const exchange = getErrorExchange(
            ((value: string) => value) as never,
            { url: 'http://example.com' } as never,
        )({ forward: () => fromValue(result) } as never);

        const response = await toPromise(exchange(fromValue(operation)));
        const pageResponse = handleServerSideErrorResponseForFriendlyUrls(
            response.error,
            response.data,
            { res: { statusCode: 200 } } as GetServerSidePropsContext,
            'http://example.com',
        );

        expect(response).toBe(result);
        expect(pageResponse).toEqual({ notFound: true });
        expect(logException).not.toHaveBeenCalled();
        expect(showErrorMessage).not.toHaveBeenCalled();
    });

    test.each([false, true])(
        'still logs missing privacy policy configuration with debugging=%s',
        async (isDebugging) => {
            debugging.enabled = isDebugging;
            const operation = {
                key: 2,
                kind: 'query',
                query: parse('query SettingsQuery { settings { privacyPolicyArticleUrl } }'),
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
            )({ forward: () => fromValue({ operation, error }) } as never);

            await toPromise(exchange(fromValue(operation)));

            expect(logException).toHaveBeenCalledOnce();
            expect(showErrorMessage).not.toHaveBeenCalled();
        },
    );
});
