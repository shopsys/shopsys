import { GtmEventType } from 'gtm/enums/GtmEventType';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockSha256 = vi.fn();

vi.mock('utils/hash/sha256', () => ({
    sha256: (...args: unknown[]) => mockSha256(...args),
}));

describe('gtmSafePushEvent', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        (window as any).dataLayer = [];
    });

    test('pushes events without email synchronously', () => {
        gtmSafePushEvent({
            _clear: true,
            ecommerce: {
                id: 'order-1',
                isPaymentSuccessful: false,
                paymentRetryCount: 0,
                paymentType: 'GoPay',
            },
            event: GtmEventType.payment,
        } as any);

        expect((window as any).dataLayer).toHaveLength(1);
        expect((window as any).dataLayer[0].event).toBe('ec.payment');
        expect(mockSha256).not.toHaveBeenCalled();
    });

    test('hashes user email before pushing event', async () => {
        mockSha256.mockResolvedValue('hashed-email');

        gtmSafePushEvent({
            event: GtmEventType.login,
            user: {
                email: 'shopper@example.com',
            },
        } as any);

        expect((window as any).dataLayer).toHaveLength(0);

        await new Promise((resolve) => setTimeout(resolve, 0));

        expect(mockSha256).toHaveBeenCalledWith('shopper@example.com');
        expect((window as any).dataLayer).toHaveLength(1);
        expect((window as any).dataLayer[0].user.emailHash).toBe('hashed-email');
    });
});
