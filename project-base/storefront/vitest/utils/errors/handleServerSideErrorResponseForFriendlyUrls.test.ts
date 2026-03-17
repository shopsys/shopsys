import { GetServerSidePropsContext } from 'next';
import { CombinedError } from 'urql';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/errors/isWithErrorDebugging', () => ({
    isWithErrorDebugging: false,
}));

vi.mock('utils/auth/getLoginUrlWithRedirect', () => ({
    getLoginUrlWithRedirect: () => '/login?r=redirect-target',
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: string[]) => urls,
}));

const createMockContext = (statusCode = 200): GetServerSidePropsContext =>
    ({
        req: { headers: {} },
        res: { statusCode },
        resolvedUrl: '/test-url',
    }) as unknown as GetServerSidePropsContext;

const createCombinedError = (
    overrides: {
        status?: number;
        graphQLErrors?: Array<{ extensions: Record<string, unknown>; message?: string }>;
    } = {},
): CombinedError => {
    const error = new CombinedError({
        graphQLErrors:
            overrides.graphQLErrors?.map((e) => ({
                message: e.message ?? 'Error',
                extensions: e.extensions,
            })) ?? [],
    });

    (error as any).response = { status: overrides.status ?? 200 };

    return error;
};

describe('handleServerSideErrorResponseForFriendlyUrls', () => {
    test('returns null when there is no error and data exists', () => {
        const result = handleServerSideErrorResponseForFriendlyUrls(
            undefined,
            { id: 1 },
            createMockContext(),
            'https://example.com',
        );
        expect(result).toBeNull();
    });

    test('returns redirect to login on 401', () => {
        const error = createCombinedError({ status: 401 });
        const result = handleServerSideErrorResponseForFriendlyUrls(
            error,
            undefined,
            createMockContext(),
            'https://example.com',
        );

        expect(result).toEqual({
            redirect: {
                destination: '/login?r=redirect-target',
                permanent: false,
            },
        });
    });

    test('throws on 500 server error (debugging off)', () => {
        const error = createCombinedError({
            graphQLErrors: [{ extensions: { code: 500 } }],
        });

        expect(() =>
            handleServerSideErrorResponseForFriendlyUrls(error, undefined, createMockContext(), 'https://example.com'),
        ).toThrow('Internal Server Error');
    });

    test('returns notFound when data is missing (404)', () => {
        const result = handleServerSideErrorResponseForFriendlyUrls(
            undefined,
            undefined,
            createMockContext(),
            'https://example.com',
        );
        expect(result).toEqual({ notFound: true });
    });

    test('returns notFound when data is null and error is undefined', () => {
        const result = handleServerSideErrorResponseForFriendlyUrls(
            undefined,
            null,
            createMockContext(),
            'https://example.com',
        );
        expect(result).toEqual({ notFound: true });
    });

    test('returns null on 503 maintenance (does not override maintenance mode)', () => {
        const result = handleServerSideErrorResponseForFriendlyUrls(
            undefined,
            undefined,
            createMockContext(503),
            'https://example.com',
        );
        expect(result).toBeNull();
    });

    test('returns redirect on price filter error', () => {
        const error = createCombinedError({
            graphQLErrors: [
                {
                    extensions: { code: 200 },
                    message: 'Filtering by price is not allowed for current user.',
                },
            ],
        });
        (error.graphQLErrors[0].extensions as any).userCode = 'access-denied';

        const result = handleServerSideErrorResponseForFriendlyUrls(
            error,
            undefined,
            createMockContext(),
            'https://example.com',
            'my-category',
        );
        expect(result).toEqual({
            redirect: {
                destination: 'https://example.com/my-category',
                permanent: false,
            },
        });
    });

    test('returns redirect on price ordering error', () => {
        const error = createCombinedError({
            graphQLErrors: [
                {
                    extensions: { code: 200 },
                    message: 'Ordering by price is not allowed for current user.',
                },
            ],
        });
        (error.graphQLErrors[0].extensions as any).userCode = 'access-denied';

        const result = handleServerSideErrorResponseForFriendlyUrls(
            error,
            undefined,
            createMockContext(),
            'https://example.com',
            'my-category',
        );
        expect(result).toEqual({
            redirect: {
                destination: 'https://example.com/my-category',
                permanent: false,
            },
        });
    });

    test('returns null when error exists but data also exists (partial response)', () => {
        const error = createCombinedError({
            graphQLErrors: [{ extensions: { code: 200 }, message: 'Some warning' }],
        });

        const result = handleServerSideErrorResponseForFriendlyUrls(
            error,
            { id: 1 },
            createMockContext(),
            'https://example.com',
        );
        expect(result).toBeNull();
    });

    test('returns notFound when error has non-500 graphql error and no data', () => {
        const error = createCombinedError({
            graphQLErrors: [{ extensions: { code: 404 }, message: 'Not found' }],
        });

        const result = handleServerSideErrorResponseForFriendlyUrls(
            error,
            undefined,
            createMockContext(),
            'https://example.com',
        );
        expect(result).toEqual({ notFound: true });
    });
});

describe('handleServerSideErrorResponseForFriendlyUrls (debugging on)', () => {
    beforeEach(() => {
        vi.resetModules();

        vi.doMock('utils/errors/isWithErrorDebugging', () => ({
            isWithErrorDebugging: true,
        }));

        vi.doMock('utils/auth/getLoginUrlWithRedirect', () => ({
            getLoginUrlWithRedirect: () => '/login?r=redirect-target',
        }));

        vi.doMock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
            getInternationalizedStaticUrls: (urls: string[]) => urls,
        }));
    });

    afterEach(() => {
        vi.resetModules();
        vi.clearAllMocks();
    });

    test('throws with debug JSON on 500 when debugging is enabled', async () => {
        const { handleServerSideErrorResponseForFriendlyUrls: fnWithDebug } = await import(
            'utils/errors/handleServerSideErrorResponseForFriendlyUrls'
        );

        const error = createCombinedError({
            graphQLErrors: [{ extensions: { code: 500 }, message: 'DB connection failed' }],
        });

        expect(() => fnWithDebug(error, undefined, createMockContext(), 'https://example.com')).toThrow(
            /DB connection failed/,
        );
    });
});
