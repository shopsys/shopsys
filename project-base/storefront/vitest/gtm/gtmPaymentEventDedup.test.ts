import { buildPaymentAttemptKey, canEmitPaymentEvent, markPaymentEventEmitted } from 'gtm/utils/gtmPaymentEventDedup';
import { beforeEach, describe, expect, test } from 'vitest';

describe('gtmPaymentEventDedup', () => {
    beforeEach(() => {
        sessionStorage.clear();
    });

    test('blocks repeated final event for the same payment attempt', () => {
        const attemptKey = buildPaymentAttemptKey({
            orderUuid: 'order-uuid',
            paymentRetryCount: 0,
        });

        expect(canEmitPaymentEvent('final', attemptKey)).toBe(true);

        markPaymentEventEmitted('final', attemptKey);

        expect(canEmitPaymentEvent('final', attemptKey)).toBe(false);
    });
});
