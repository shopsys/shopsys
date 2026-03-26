import { DomainConfigType } from 'utils/domain/domainConfig';
import {
    buildPaymentConfirmationUrlFromSession,
    getGoPayPaymentSession,
    getGoPayPaymentSessionForOrder,
    markGoPayPaymentSessionForRedirectOnly,
    removeGoPayPaymentSession,
    saveGoPayPaymentSession,
    shouldOpenGoPayAsRedirectOnly,
} from 'utils/goPayPaymentSessionStorage';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const STORAGE_KEY = 'goPayPaymentSession';

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (_urls: unknown[], domainUrl: string) => {
        if (domainUrl.endsWith('/sk')) {
            return ['/potvrdenie-platby-objednavky'];
        }

        return ['/order-payment-confirmation'];
    },
}));

describe('goPayPaymentSessionStorage', () => {
    const csDomainConfig = { url: 'http://127.0.0.1:8000', defaultLocale: 'cs' } as DomainConfigType;
    const skDomainConfig = { url: 'http://127.0.0.1:8000/sk', defaultLocale: 'sk' } as DomainConfigType;

    beforeEach(() => {
        localStorage.clear();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    test('saves and reads GoPay payment session data for the same domain', () => {
        saveGoPayPaymentSession({
            orderUuid: 'order-uuid',
            orderUrlHash: 'order-hash',
            orderPaymentStatusPageValidityHash: 'validity-hash',
            domainUrl: 'http://127.0.0.1:8000',
        });

        const session = getGoPayPaymentSession('http://127.0.0.1:8000');

        expect(session).toMatchObject({
            orderUuid: 'order-uuid',
            orderUrlHash: 'order-hash',
            orderPaymentStatusPageValidityHash: 'validity-hash',
            domainUrl: 'http://127.0.0.1:8000',
            forceRedirectAfterInlineReturn: false,
        });
        expect(typeof session?.timestamp).toBe('number');
    });

    test('returns null when requested from a different domain', () => {
        saveGoPayPaymentSession({
            orderUuid: 'order-uuid',
            orderPaymentStatusPageValidityHash: 'validity-hash',
            domainUrl: 'http://127.0.0.1:8000',
        });

        const session = getGoPayPaymentSession('https://example.com');

        expect(session).toBeNull();
    });

    test('removes invalid JSON payload from localStorage', () => {
        localStorage.setItem(STORAGE_KEY, '{invalid-json');

        const session = getGoPayPaymentSession();

        expect(session).toBeNull();
        expect(localStorage.getItem(STORAGE_KEY)).toBeNull();
    });

    test('removes saved session', () => {
        saveGoPayPaymentSession({
            orderUuid: 'order-uuid',
            orderPaymentStatusPageValidityHash: 'validity-hash',
            domainUrl: 'http://127.0.0.1:8000',
        });

        removeGoPayPaymentSession();

        expect(localStorage.getItem(STORAGE_KEY)).toBeNull();
    });

    test('returns null and clears stale session older than 30 minutes', () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date('2026-02-20T10:00:00.000Z'));

        saveGoPayPaymentSession({
            orderUuid: 'order-uuid',
            orderPaymentStatusPageValidityHash: 'validity-hash',
            domainUrl: 'http://127.0.0.1:8000',
        });

        vi.setSystemTime(new Date('2026-02-20T10:31:00.000Z'));

        const session = getGoPayPaymentSession('http://127.0.0.1:8000');

        expect(session).toBeNull();
        expect(localStorage.getItem(STORAGE_KEY)).toBeNull();
    });

    test('marks matching session as redirect-only for future GoPay retries', () => {
        saveGoPayPaymentSession({
            orderUuid: 'order-uuid',
            orderPaymentStatusPageValidityHash: 'validity-hash',
            domainUrl: 'http://127.0.0.1:8000',
        });

        const wasMarked = markGoPayPaymentSessionForRedirectOnly('http://127.0.0.1:8000', 'order-uuid');
        const session = getGoPayPaymentSessionForOrder('http://127.0.0.1:8000', 'order-uuid');

        expect(wasMarked).toBe(true);
        expect(session?.forceRedirectAfterInlineReturn).toBe(true);
        expect(shouldOpenGoPayAsRedirectOnly('http://127.0.0.1:8000', 'order-uuid')).toBe(true);
    });

    test('does not mark redirect-only for a different order', () => {
        saveGoPayPaymentSession({
            orderUuid: 'other-order',
            orderPaymentStatusPageValidityHash: 'validity-hash',
            domainUrl: 'http://127.0.0.1:8000',
        });

        const wasMarked = markGoPayPaymentSessionForRedirectOnly('http://127.0.0.1:8000', 'order-uuid');

        expect(wasMarked).toBe(false);
        expect(shouldOpenGoPayAsRedirectOnly('http://127.0.0.1:8000', 'order-uuid')).toBe(false);
    });

    test('ignores localStorage write errors when saving session', () => {
        const setItemSpy = vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {
            throw new Error('storage unavailable');
        });

        expect(() =>
            saveGoPayPaymentSession({
                orderUuid: 'order-uuid',
                orderPaymentStatusPageValidityHash: 'validity-hash',
                domainUrl: 'http://127.0.0.1:8000',
            }),
        ).not.toThrow();

        setItemSpy.mockRestore();
    });

    describe('buildPaymentConfirmationUrlFromSession', () => {
        test('builds confirmation URL without locale prefix for default-locale domain', () => {
            saveGoPayPaymentSession({
                orderUuid: 'order-uuid',
                orderUrlHash: 'order-hash',
                orderPaymentStatusPageValidityHash: 'validity-hash',
                domainUrl: 'http://127.0.0.1:8000',
            });

            const url = buildPaymentConfirmationUrlFromSession(csDomainConfig, 'order-uuid');

            expect(url).toBe(
                '/order-payment-confirmation?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash&orderUrlHash=order-hash',
            );
        });

        test('builds confirmation URL with locale prefix for locale-path domain', () => {
            saveGoPayPaymentSession({
                orderUuid: 'order-uuid',
                orderUrlHash: 'order-hash',
                orderPaymentStatusPageValidityHash: 'validity-hash',
                domainUrl: 'http://127.0.0.1:8000/sk',
            });

            const url = buildPaymentConfirmationUrlFromSession(skDomainConfig, 'order-uuid');

            expect(url).toBe(
                '/sk/potvrdenie-platby-objednavky?orderIdentifier=order-uuid&orderPaymentStatusPageValidityHash=validity-hash&orderUrlHash=order-hash',
            );
        });

        test('returns null when no session exists for the order', () => {
            const url = buildPaymentConfirmationUrlFromSession(csDomainConfig, 'missing-order');

            expect(url).toBeNull();
        });
    });
});
