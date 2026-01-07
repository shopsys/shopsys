import { isFlashMessageError, isNoFlashMessageError, isNoLogError } from 'utils/errors/applicationErrors';
import { describe, expect, test } from 'vitest';

describe('isFlashMessageError', () => {
    test.each([
        'default',
        'cart-not-found',
        'max-allowed-limit',
        'packetery-address-id-invalid',
        'invalid-credentials',
        'invalid-refresh-token',
        'order-emails-not-sent',
        'order-empty-cart',
        'personal-data-request-type-invalid',
        'order-not-found',
        'complaint-not-found',
        'personal-data-hash-invalid',
        'product-price-missing',
        'store-not-found',
        'product-not-found',
        'handling-with-logged-customer-comparison',
        'cannot-remove-own-customer-user',
        'access-denied',
        'invalid-quantity',
        'register-by-order-is-not-possible',
        'too-many-login-attempts',
        'company-already-registered',
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
        'blog-category-not-found',
        'blog-article-not-found',
        'category-not-found',
        'COMPARISON-product-list-not-found',
        'WISHLIST-product-list-not-found',
        'unable-to-generate-breadcrumb-items',
        'article-not-found',
        'article-not-found-terms-and-conditions',
        'article-not-found-privacy-policy',
        'article-not-found-user-consent-policy',
    ])('should return true for no-flash-message code: %s', (code) => {
        expect(isNoFlashMessageError(code)).toBe(true);
    });

    test('should return false for flash-message codes', () => {
        expect(isNoFlashMessageError('invalid-credentials')).toBe(false);
        expect(isNoFlashMessageError('cart-not-found')).toBe(false);
        expect(isNoFlashMessageError('order-not-found')).toBe(false);
    });

    test('should return false for no-log codes', () => {
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
        'no-result-found-for-slug',
        'invalid-token',
        'COMPARISON-product-not-in-list',
        'COMPARISON-product-already-in-list',
        'seo-page-not-found',
        'order-sent-page-not-available',
        'WISHLIST-product-already-in-list',
        'WISHLIST-product-not-in-list',
        'invalid-account-or-password',
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
            'default',
            'cart-not-found',
            'max-allowed-limit',
            'packetery-address-id-invalid',
            'invalid-credentials',
            'invalid-refresh-token',
            'order-emails-not-sent',
            'order-empty-cart',
            'personal-data-request-type-invalid',
            'blog-category-not-found',
            'blog-article-not-found',
            'category-not-found',
            'order-not-found',
            'complaint-not-found',
            'personal-data-hash-invalid',
            'product-price-missing',
            'no-result-found-for-slug',
            'store-not-found',
            'invalid-token',
            'product-not-found',
            'handling-with-logged-customer-comparison',
            'COMPARISON-product-list-not-found',
            'COMPARISON-product-not-in-list',
            'COMPARISON-product-already-in-list',
            'seo-page-not-found',
            'order-sent-page-not-available',
            'WISHLIST-product-list-not-found',
            'WISHLIST-product-already-in-list',
            'WISHLIST-product-not-in-list',
            'unable-to-generate-breadcrumb-items',
            'article-not-found',
            'article-not-found-terms-and-conditions',
            'article-not-found-privacy-policy',
            'article-not-found-user-consent-policy',
            'cannot-remove-own-customer-user',
            'access-denied',
            'invalid-quantity',
            'register-by-order-is-not-possible',
            'too-many-login-attempts',
            'invalid-account-or-password',
            'company-already-registered',
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
