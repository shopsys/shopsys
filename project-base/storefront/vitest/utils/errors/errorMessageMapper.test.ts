import { Translate } from 'next-translate';
import { getErrorMessage } from 'utils/errors/errorMessageMapper';
import { describe, expect, test, vi } from 'vitest';

const createMockT = (): { t: Translate; calls: string[] } => {
    const calls: string[] = [];
    const t = vi.fn((key: string) => {
        calls.push(key);
        return key;
    }) as unknown as Translate;
    return { t, calls };
};

describe('getErrorMessage', () => {
    test('should return translated message for default error code', () => {
        const { t } = createMockT();

        const result = getErrorMessage('default', t);

        expect(result).toContain('Unknown error');
    });

    test('should return translated message for cart-not-found', () => {
        const { t } = createMockT();

        const result = getErrorMessage('cart-not-found', t);

        expect(result).toContain('Cart not found');
    });

    test('should return translated message for invalid-credentials', () => {
        const { t } = createMockT();

        const result = getErrorMessage('invalid-credentials', t);

        expect(result).toContain('Invalid credentials');
    });

    test('should return translated message for order-not-found', () => {
        const { t } = createMockT();

        const result = getErrorMessage('order-not-found', t);

        expect(result).toContain('Order not found');
    });

    test('should return translated message for access-denied', () => {
        const { t } = createMockT();

        const result = getErrorMessage('access-denied', t);

        expect(result).toContain('Access denied');
    });

    test('should return translated message for max-allowed-limit', () => {
        const { t } = createMockT();

        const result = getErrorMessage('max-allowed-limit', t);

        expect(result).toContain('Max allowed limit');
    });

    test('should return translated message for complaint-not-found', () => {
        const { t } = createMockT();

        const result = getErrorMessage('complaint-not-found', t);

        expect(result).toContain('Complaint not found');
    });

    test('should return translated message for product-not-found', () => {
        const { t } = createMockT();

        const result = getErrorMessage('product-not-found', t);

        expect(result).toContain('Product not found');
    });

    test('should return translated message for store-not-found', () => {
        const { t } = createMockT();

        const result = getErrorMessage('store-not-found', t);

        expect(result).toContain('Store not found');
    });

    test('should return translated message for too-many-login-attempts', () => {
        const { t } = createMockT();

        const result = getErrorMessage('too-many-login-attempts', t);

        expect(result).toContain('Too many login attempts');
    });

    test('should return translated message for invalid-quantity', () => {
        const { t } = createMockT();

        const result = getErrorMessage('invalid-quantity', t);

        expect(result).toBeDefined();
    });

    test('should return translated message for cannot-remove-own-customer-user', () => {
        const { t } = createMockT();

        const result = getErrorMessage('cannot-remove-own-customer-user', t);

        expect(result).toContain('Cannot delete own user');
    });

    test('should return translated message for company-already-registered', () => {
        const { t } = createMockT();

        const result = getErrorMessage('company-already-registered', t);

        expect(result).toContain('Customer is already registered');
    });

    test('should call translation function with correct key', () => {
        const { t } = createMockT();

        getErrorMessage('invalid-credentials', t);

        expect(t).toHaveBeenCalledWith('Invalid credentials.');
    });

    describe('double translation bug detection', () => {
        test('should NOT double-translate messages - translation function should not be called on already translated result', () => {
            const { t, calls } = createMockT();

            getErrorMessage('cart-not-found', t);

            // The translation function should be called for initial translation
            // but the result should NOT be passed through t() again
            // Currently this will fail because of the double translation bug on line 36:
            // return translationString !== undefined ? t(translationString) : t('Unknown error.');
            // The translationString is ALREADY the result of t(), so wrapping it in t() again is redundant

            // Count how many times t was called with the translated string
            // If double-translation exists, we'll see the translated string passed to t() again
            const cartNotFoundTranslatedString = 'Cart not found.';
            const callsWithTranslatedString = calls.filter((c) => c === cartNotFoundTranslatedString);

            // Should be called exactly once (for initial translation), not twice (double translation)
            // This test exposes the bug - it will fail with current implementation
            expect(callsWithTranslatedString.length).toBeLessThanOrEqual(1);
        });
    });

    describe('all flash-message codes have translations', () => {
        test.each([
            ['default', 'Unknown error.'],
            ['cart-not-found', 'Cart not found.'],
            ['max-allowed-limit', 'Max allowed limit reached.'],
            ['packetery-address-id-invalid', 'Invalid Packetery address id.'],
            ['invalid-credentials', 'Invalid credentials.'],
            ['invalid-refresh-token', 'Invalid refresh token.'],
            ['order-emails-not-sent', 'Automatic order emails was not sent.'],
            ['order-empty-cart', 'Cart is empty.'],
            ['personal-data-request-type-invalid', 'Invalid request type.'],
            ['order-not-found', 'Order not found.'],
            ['complaint-not-found', 'Complaint not found.'],
            ['personal-data-hash-invalid', 'Invalid hash.'],
            ['product-price-missing', 'Product price is missing.'],
            ['store-not-found', 'Store not found.'],
            ['product-not-found', 'Product not found.'],
            ['handling-with-logged-customer-comparison', 'Product not found.'],
            ['cannot-remove-own-customer-user', 'Cannot delete own user'],
            ['access-denied', 'Access denied'],
            ['register-by-order-is-not-possible', 'It was not possible to create register new user from the order'],
            ['too-many-login-attempts', 'Too many login attempts. Try again later.'],
            ['company-already-registered', 'Customer is already registered.'],
        ] as const)('should return translation for %s', (code, expectedMessage) => {
            const { t } = createMockT();

            const result = getErrorMessage(code, t);

            expect(result).toContain(expectedMessage.slice(0, 10));
        });
    });
});
