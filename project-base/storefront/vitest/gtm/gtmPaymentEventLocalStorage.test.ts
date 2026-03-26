import {
    getGtmPendingPaymentFromLocalStorage,
    removeGtmPendingPaymentFromLocalStorage,
    saveGtmPendingPaymentInLocalStorage,
} from 'gtm/utils/gtmPaymentEventLocalStorage';
import { beforeEach, describe, expect, test } from 'vitest';

const STORAGE_KEY = 'gtmPendingPayment';

describe('gtmPaymentEventLocalStorage', () => {
    beforeEach(() => {
        localStorage.clear();
    });

    test('saves and reads pending payment data for the same domain', () => {
        saveGtmPendingPaymentInLocalStorage({
            orderUuid: 'order-uuid',
            orderNumber: '1234567890',
            paymentName: 'GoPay - Payment by Card',
            paymentTransactionsCount: 2,
            domainUrl: 'http://127.0.0.1:8000',
        });

        const pendingPayment = getGtmPendingPaymentFromLocalStorage('http://127.0.0.1:8000');

        expect(pendingPayment).toMatchObject({
            orderUuid: 'order-uuid',
            orderNumber: '1234567890',
            paymentName: 'GoPay - Payment by Card',
            paymentTransactionsCount: 2,
            domainUrl: 'http://127.0.0.1:8000',
        });
        expect(typeof pendingPayment?.timestamp).toBe('number');
    });

    test('returns null for domain mismatch', () => {
        saveGtmPendingPaymentInLocalStorage({
            orderUuid: 'order-uuid',
            orderNumber: '1234567890',
            paymentName: 'GoPay - Payment by Card',
            domainUrl: 'http://127.0.0.1:8000',
        });

        const pendingPayment = getGtmPendingPaymentFromLocalStorage('https://example.com');

        expect(pendingPayment).toBeNull();
    });

    test('matches domain with and without trailing slash', () => {
        saveGtmPendingPaymentInLocalStorage({
            orderUuid: 'order-uuid',
            orderNumber: '1234567890',
            paymentName: 'GoPay - Payment by Card',
            domainUrl: 'http://127.0.0.1:8000/',
        });

        const pendingPayment = getGtmPendingPaymentFromLocalStorage('http://127.0.0.1:8000');

        expect(pendingPayment).toMatchObject({
            orderUuid: 'order-uuid',
            domainUrl: 'http://127.0.0.1:8000',
        });
    });

    test('removes invalid JSON payload from localStorage', () => {
        localStorage.setItem(STORAGE_KEY, '{invalid-json');

        const pendingPayment = getGtmPendingPaymentFromLocalStorage();

        expect(pendingPayment).toBeNull();
        expect(localStorage.getItem(STORAGE_KEY)).toBeNull();
    });

    test('removes pending payment from localStorage', () => {
        saveGtmPendingPaymentInLocalStorage({
            orderUuid: 'order-uuid',
            orderNumber: '1234567890',
            paymentName: 'GoPay - Payment by Card',
            domainUrl: 'http://127.0.0.1:8000',
        });

        removeGtmPendingPaymentFromLocalStorage();

        expect(localStorage.getItem(STORAGE_KEY)).toBeNull();
    });
});
