import { isFlashMessageError, isNoFlashMessageError, isNoLogError } from 'utils/errors/applicationErrors';
import { describe, expect, test } from 'vitest';

describe('isFlashMessageError', () => {
    test.each([
        'default',
        'access-denied',
        'cannot-remove-own-customer-user',
        'cart-not-found',
        'company-already-registered',
        'complaint-not-found',
        'handling-with-logged-customer-comparison',
        'invalid-credentials',
        'invalid-quantity',
        'invalid-refresh-token',
        'max-allowed-limit',
        'max-transaction-count-reached',
        'order-emails-not-sent',
        'order-empty-cart',
        'order-not-found',
        'packetery-address-id-invalid',
        'personal-data-hash-invalid',
        'personal-data-request-type-invalid',
        'product-not-found',
        'product-price-missing',
        'register-by-order-is-not-possible',
        'store-not-found',
        'too-many-login-attempts',
        'too-many-store-search-attempts',
    ])('should return true for flash-message code: %s', (code) => {
        expect(isFlashMessageError(code)).toBe(true);
    });

    test('should return false for no-flash-message codes', () => {
        expect(isFlashMessageError('category-not-found')).toBe(false);
        expect(isFlashMessageError('blog-category-not-found')).toBe(false);
        expect(isFlashMessageError('article-not-found')).toBe(false);
    });

    test('should return false for no-log codes', () => {
        expect(isFlashMessageError('no-result-found-for-slug')).toBe(false);
        expect(isFlashMessageError('expired-token')).toBe(false);
        expect(isFlashMessageError('invalid-token')).toBe(false);
        expect(isFlashMessageError('seo-page-not-found')).toBe(false);
    });

    test('should return false for unknown codes', () => {
        expect(isFlashMessageError('unknown-code')).toBe(false);
        expect(isFlashMessageError('random-error')).toBe(false);
        expect(isFlashMessageError('')).toBe(false);
    });
});

describe('isNoFlashMessageError', () => {
    test.each([
        'advert-position-without-category',
        'article-not-found',
        'article-not-found-privacy-policy',
        'article-not-found-terms-and-conditions',
        'article-not-found-user-consent-policy',
        'blog-article-not-found',
        'blog-category-not-found',
        'brand-not-found',
        'cart-item-invalid',
        'cart-unavailable',
        'category-not-found',
        'COMPARISON-product-list-not-found',
        'country-not-found',
        'customer-user-not-found',
        'customer-user-not-logged',
        'delivery-address-not-found',
        'flag-not-found',
        'invalid-access',
        'invalid-argument',
        'invalid-find-criteria-for-product-list',
        'last-customer-user-with-default-role-group',
        'mail-failed',
        'missing-complaint-items',
        'order-already-paid',
        'order-cancelled',
        'order-item-not-found',
        'order-process-payment',
        'order-withdrawal-already-requested',
        'order-withdrawal-deadline-passed',
        'payment-not-found',
        'product-already-in-list',
        'product-list-not-found',
        'product-not-in-list',
        'ready-category-seo-mix-not-found',
        'transport-not-found',
        'unable-to-generate-breadcrumb-items',
        'WISHLIST-product-list-not-found',
    ])('should return true for no-flash-message code: %s', (code) => {
        expect(isNoFlashMessageError(code)).toBe(true);
    });

    test('should return false for flash-message codes', () => {
        expect(isNoFlashMessageError('invalid-credentials')).toBe(false);
        expect(isNoFlashMessageError('cart-not-found')).toBe(false);
        expect(isNoFlashMessageError('order-not-found')).toBe(false);
    });

    test('should return false for no-log codes', () => {
        expect(isNoFlashMessageError('expired-token')).toBe(false);
        expect(isNoFlashMessageError('no-result-found-for-slug')).toBe(false);
        expect(isNoFlashMessageError('invalid-token')).toBe(false);
    });

    test('should return false for unknown codes', () => {
        expect(isNoFlashMessageError('unknown-code')).toBe(false);
        expect(isNoFlashMessageError('')).toBe(false);
    });
});

describe('isNoLogError', () => {
    test.each([
        'COMPARISON-product-already-in-list',
        'COMPARISON-product-not-in-list',
        'expired-token',
        'invalid-account-or-password',
        'invalid-token',
        'no-result-found-for-slug',
        'order-sent-page-not-available',
        'seo-page-not-found',
        'WISHLIST-product-already-in-list',
        'WISHLIST-product-not-in-list',
    ])('should return true for no-log code: %s', (code) => {
        expect(isNoLogError(code)).toBe(true);
    });

    test('should return false for flash-message codes', () => {
        expect(isNoLogError('invalid-credentials')).toBe(false);
        expect(isNoLogError('cart-not-found')).toBe(false);
        expect(isNoLogError('order-not-found')).toBe(false);
    });

    test('should return false for no-flash-message codes', () => {
        expect(isNoLogError('category-not-found')).toBe(false);
        expect(isNoLogError('blog-category-not-found')).toBe(false);
    });

    test('should return false for unknown codes', () => {
        expect(isNoLogError('unknown-code')).toBe(false);
        expect(isNoLogError('')).toBe(false);
    });
});

describe('error code classification coverage', () => {
    test('each error code should belong to exactly one category', () => {
        const allCodes = [
            // Flash-message codes
            'default',
            'access-denied',
            'cannot-remove-own-customer-user',
            'cart-not-found',
            'company-already-registered',
            'complaint-not-found',
            'handling-with-logged-customer-comparison',
            'invalid-credentials',
            'invalid-quantity',
            'invalid-refresh-token',
            'max-allowed-limit',
            'max-transaction-count-reached',
            'order-emails-not-sent',
            'order-empty-cart',
            'order-not-found',
            'packetery-address-id-invalid',
            'personal-data-hash-invalid',
            'personal-data-request-type-invalid',
            'product-not-found',
            'product-price-missing',
            'register-by-order-is-not-possible',
            'store-not-found',
            'too-many-login-attempts',
            'too-many-store-search-attempts',
            // No-flash-message codes (logged but no toast)
            'advert-position-without-category',
            'article-not-found',
            'article-not-found-privacy-policy',
            'article-not-found-terms-and-conditions',
            'article-not-found-user-consent-policy',
            'blog-article-not-found',
            'blog-category-not-found',
            'brand-not-found',
            'cart-item-invalid',
            'cart-unavailable',
            'category-not-found',
            'COMPARISON-product-list-not-found',
            'country-not-found',
            'customer-user-not-found',
            'customer-user-not-logged',
            'delivery-address-not-found',
            'flag-not-found',
            'invalid-access',
            'invalid-argument',
            'invalid-find-criteria-for-product-list',
            'last-customer-user-with-default-role-group',
            'mail-failed',
            'missing-complaint-items',
            'order-already-paid',
            'order-cancelled',
            'order-item-not-found',
            'order-process-payment',
            'order-withdrawal-already-requested',
            'order-withdrawal-deadline-passed',
            'payment-not-found',
            'product-already-in-list',
            'product-list-not-found',
            'product-not-in-list',
            'ready-category-seo-mix-not-found',
            'transport-not-found',
            'unable-to-generate-breadcrumb-items',
            'WISHLIST-product-list-not-found',
            // No-log codes
            'COMPARISON-product-already-in-list',
            'COMPARISON-product-not-in-list',
            'expired-token',
            'invalid-account-or-password',
            'invalid-token',
            'no-result-found-for-slug',
            'order-sent-page-not-available',
            'seo-page-not-found',
            'WISHLIST-product-already-in-list',
            'WISHLIST-product-not-in-list',
        ];

        for (const code of allCodes) {
            const isFlash = isFlashMessageError(code);
            const isNoFlash = isNoFlashMessageError(code);
            const isNoLog = isNoLogError(code);

            const categoryCount = [isFlash, isNoFlash, isNoLog].filter(Boolean).length;

            expect(categoryCount).toBe(1);
        }
    });
});
