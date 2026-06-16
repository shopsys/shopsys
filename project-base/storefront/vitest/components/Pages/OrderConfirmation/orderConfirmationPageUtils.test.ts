import { getIsWaitingForPaymentStatusUpdate } from 'components/Pages/OrderConfirmation/orderConfirmationPageUtils';
import { describe, expect, test } from 'vitest';

describe('getIsWaitingForPaymentStatusUpdate', () => {
    test('waits while requested payment status update has neither data nor error', () => {
        expect(getIsWaitingForPaymentStatusUpdate(true, false, false)).toBe(true);
    });

    test('stops waiting after payment status update resolves with data or error', () => {
        expect(getIsWaitingForPaymentStatusUpdate(true, false, true)).toBe(false);
        expect(getIsWaitingForPaymentStatusUpdate(true, true, false)).toBe(false);
        expect(getIsWaitingForPaymentStatusUpdate(false, false, false)).toBe(false);
    });
});
