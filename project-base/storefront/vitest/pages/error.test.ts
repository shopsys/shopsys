import { NextPageContext } from 'next';
import ErrorPage from 'pages/_error';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { logException } from 'utils/errors/logException';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Pages/ErrorPage/Error404Content', () => ({ Error404Content: () => null }));
vi.mock('components/Pages/ErrorPage/Error500Content', () => ({ Error500Content: () => null }));
vi.mock('components/Pages/ErrorPage/MinimalErrorContent', () => ({ MinimalErrorContent: () => null }));
vi.mock('next-translate/loadNamespaces', () => ({ default: vi.fn().mockResolvedValue({}) }));
vi.mock('utils/cookies/cookiesStore', () => ({ getCookiesStoreState: vi.fn() }));
vi.mock('utils/domain/domainConfig', () => ({ getDomainConfig: vi.fn() }));
vi.mock('utils/domain/getClientDomainConfig', () => ({
    getClientDomainConfig: () => {
        throw new Error('Unknown domain');
    },
}));
vi.mock('utils/errors/logException', () => ({ logException: vi.fn() }));

describe('ErrorPage.getInitialProps', () => {
    test('reports the original runtime exception while keeping serializable page props', async () => {
        const error = new TypeError('Page rendering failed');
        const context = { err: error } as NextPageContext;

        const props = await ErrorPage.getInitialProps!(context);

        expect(vi.mocked(logException).mock.calls[0][0]).toBe(error);
        expect(logException).toHaveBeenCalledWith(error, {
            location: 'ErrorPage.getInitialProps',
            statusCode: 500,
        });
        expect(props).toEqual({
            statusCode: 500,
            err: JSON.stringify({ name: error.name, message: error.message, stack: error.stack }),
        });
    });

    test('reports an Error with the status code when there is no original exception', async () => {
        const context = { res: { statusCode: 502 } } as NextPageContext;

        await ErrorPage.getInitialProps!(context);

        expect(logException).toHaveBeenCalledWith(new Error('Unknown error'), {
            location: 'ErrorPage.getInitialProps',
            statusCode: 502,
        });
    });

    test('does not report a not-found response', async () => {
        const context = { res: { statusCode: 404 } } as NextPageContext;

        await ErrorPage.getInitialProps!(context);

        expect(logException).not.toHaveBeenCalled();
    });

    test('preserves a domain configuration exception when falling back to minimal props', async () => {
        const error = new Error('Domain configuration failed');
        const context = { req: {}, res: { statusCode: 500 } } as NextPageContext;
        vi.mocked(getDomainConfig).mockImplementationOnce(() => {
            throw error;
        });

        const props = await ErrorPage.getInitialProps!(context);

        expect(vi.mocked(logException).mock.calls[1][0]).toBe(error);
        expect(logException).toHaveBeenLastCalledWith(error, {
            location: 'ErrorPage.getInitialProps.getDomainConfig',
            statusCode: 500,
        });
        expect(props).toEqual({ statusCode: 500, err: undefined });
    });
});
