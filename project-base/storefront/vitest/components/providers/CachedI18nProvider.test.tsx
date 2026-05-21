import { render, waitFor } from '@testing-library/react';
import { CachedI18nProvider } from 'components/providers/CachedI18nProvider';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { logExceptionMock } = vi.hoisted(() => ({
    logExceptionMock: vi.fn(),
}));

vi.mock('next-translate/I18nProvider', () => ({
    default: ({ children, namespaces }: any) => (
        <div data-namespaces={JSON.stringify(namespaces)} data-testid="i18n-provider">
            {children}
        </div>
    ),
}));

vi.mock('utils/errors/logException', () => ({
    logException: logExceptionMock,
}));

const getRenderedNamespaces = (container: HTMLElement) => {
    const namespaces = container.querySelector('[data-testid="i18n-provider"]')?.getAttribute('data-namespaces');

    return namespaces ? JSON.parse(namespaces) : {};
};

describe('CachedI18nProvider', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        global.fetch = vi.fn();
    });

    test('reuses cached namespaces when route data omits them', async () => {
        const { container, rerender } = render(
            <CachedI18nProvider
                pageProps={{
                    __lang: 'en',
                    __namespaceNames: ['common', 'accessibility'],
                    __namespaces: {
                        common: { Save: 'Save' },
                        accessibility: { Close: 'Close' },
                    },
                    __translationVersion: '1',
                }}
            >
                Content
            </CachedI18nProvider>,
        );

        rerender(
            <CachedI18nProvider
                pageProps={{
                    __lang: 'en',
                    __namespaceNames: ['common', 'accessibility'],
                    __translationVersion: '1',
                }}
            >
                Content
            </CachedI18nProvider>,
        );

        expect(getRenderedNamespaces(container)).toEqual({
            common: { Save: 'Save' },
            accessibility: { Close: 'Close' },
        });
        expect(global.fetch).not.toHaveBeenCalled();
    });

    test('refreshes cached namespaces when translation version changes', async () => {
        vi.mocked(global.fetch)
            .mockResolvedValueOnce({ status: 200, json: vi.fn().mockResolvedValue({ Save: 'Save fresh' }) } as any)
            .mockResolvedValueOnce({ status: 200, json: vi.fn().mockResolvedValue({ Save: 'Save user' }) } as any)
            .mockResolvedValueOnce({ status: 200, json: vi.fn().mockResolvedValue({ Close: 'Close fresh' }) } as any)
            .mockResolvedValueOnce({ status: 404, json: vi.fn() } as any);

        const { container, rerender } = render(
            <CachedI18nProvider
                pageProps={{
                    __lang: 'en',
                    __namespaceNames: ['common', 'accessibility'],
                    __namespaces: {
                        common: { Save: 'Save old' },
                        accessibility: { Close: 'Close old' },
                    },
                    __translationVersion: '1',
                }}
            >
                Content
            </CachedI18nProvider>,
        );

        rerender(
            <CachedI18nProvider
                pageProps={{
                    __lang: 'en',
                    __namespaceNames: ['common', 'accessibility'],
                    __translationVersion: '2',
                }}
            >
                Content
            </CachedI18nProvider>,
        );

        await waitFor(() => {
            expect(getRenderedNamespaces(container)).toEqual({
                common: { Save: 'Save user' },
                accessibility: { Close: 'Close fresh' },
            });
        });

        expect(global.fetch).toHaveBeenCalledWith('/locales/en/common.json?v=2');
        expect(global.fetch).toHaveBeenCalledWith('/content/locales/en/common.json?v=2');
        expect(global.fetch).toHaveBeenCalledWith('/locales/en/accessibility.json?v=2');
        expect(global.fetch).toHaveBeenCalledWith('/content/locales/en/accessibility.json?v=2');
    });

    test('refreshes cached namespaces when locale changes with the same translation version', async () => {
        vi.mocked(global.fetch)
            .mockResolvedValueOnce({ status: 200, json: vi.fn().mockResolvedValue({ Save: 'Uložiť' }) } as any)
            .mockResolvedValueOnce({ status: 200, json: vi.fn().mockResolvedValue({ Save: 'Uložiť override' }) } as any)
            .mockResolvedValueOnce({ status: 200, json: vi.fn().mockResolvedValue({ Close: 'Zavrieť' }) } as any)
            .mockResolvedValueOnce({ status: 200, json: vi.fn().mockResolvedValue({}) } as any);

        const { container, rerender } = render(
            <CachedI18nProvider
                pageProps={{
                    __lang: 'en',
                    __namespaceNames: ['common', 'accessibility'],
                    __namespaces: {
                        common: { Save: 'Save' },
                        accessibility: { Close: 'Close' },
                    },
                    __translationVersion: '1',
                }}
            >
                Content
            </CachedI18nProvider>,
        );

        rerender(
            <CachedI18nProvider
                pageProps={{
                    __lang: 'sk',
                    __namespaceNames: ['common', 'accessibility'],
                    __translationVersion: '1',
                }}
            >
                Content
            </CachedI18nProvider>,
        );

        await waitFor(() => {
            expect(getRenderedNamespaces(container)).toEqual({
                common: { Save: 'Uložiť override' },
                accessibility: { Close: 'Zavrieť' },
            });
        });

        expect(global.fetch).toHaveBeenCalledWith('/locales/sk/common.json?v=1');
        expect(global.fetch).toHaveBeenCalledWith('/content/locales/sk/common.json?v=1');
        expect(global.fetch).toHaveBeenCalledWith('/locales/sk/accessibility.json?v=1');
        expect(global.fetch).toHaveBeenCalledWith('/content/locales/sk/accessibility.json?v=1');
    });

    test('keeps cached namespaces when refresh fails', async () => {
        const fetchError = new Error('Failed to fetch translations');
        vi.mocked(global.fetch).mockRejectedValue(fetchError);

        const { container, rerender } = render(
            <CachedI18nProvider
                pageProps={{
                    __lang: 'en',
                    __namespaceNames: ['common'],
                    __namespaces: {
                        common: { Save: 'Save old' },
                    },
                    __translationVersion: '1',
                }}
            >
                Content
            </CachedI18nProvider>,
        );

        rerender(
            <CachedI18nProvider
                pageProps={{
                    __lang: 'en',
                    __namespaceNames: ['common'],
                    __translationVersion: '2',
                }}
            >
                Content
            </CachedI18nProvider>,
        );

        await waitFor(() => {
            expect(logExceptionMock).toHaveBeenCalledWith(fetchError);
        });

        expect(getRenderedNamespaces(container)).toEqual({
            common: { Save: 'Save old' },
        });
    });
});
