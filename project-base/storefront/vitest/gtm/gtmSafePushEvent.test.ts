import { GtmEventType } from 'gtm/enums/GtmEventType';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { describe, expect, test, vi } from 'vitest';

const { captureExceptionMock } = vi.hoisted(() => ({
    captureExceptionMock: vi.fn(),
}));

vi.mock('@sentry/nextjs', () => ({
    captureException: captureExceptionMock,
}));

const promotionClickEvent = {
    event: GtmEventType.promotion_click,
    ecommerce: {},
    _clear: true,
};

const failToReportError = () => {
    throw new Error('Sentry reporting failed');
};

const verifyQueueRecoversAfterFailedPush = async ({ doesReportingFail }: { doesReportingFail: boolean }) => {
    const previousDataLayer = window.dataLayer;
    const pushedEvents: unknown[] = [];
    const pushError = new Error('GTM tag failed');
    const failingPageReadyPush = (event: { event: string }) => {
        if (event.event === GtmEventType.page_ready) {
            throw pushError;
        }

        return pushedEvents.push(event);
    };
    const pageReadyEvent = {
        event: GtmEventType.page_ready,
        user: { email: 'customer@example.com' },
        _clear: true,
    };
    if (doesReportingFail) {
        captureExceptionMock.mockImplementationOnce(failToReportError);
    }

    window.dataLayer = Object.assign([], { push: failingPageReadyPush });

    try {
        gtmSafePushEvent(pageReadyEvent);
        gtmSafePushEvent(promotionClickEvent);

        await vi.waitFor(() => expect(pushedEvents).toEqual([promotionClickEvent]));
        expect(captureExceptionMock).toHaveBeenCalledExactlyOnceWith(pushError);

        gtmSafePushEvent(promotionClickEvent);

        expect(pushedEvents).toEqual([promotionClickEvent, promotionClickEvent]);
    } finally {
        window.dataLayer = previousDataLayer;
    }
};

describe('gtmSafePushEvent', () => {
    test('pushes events without an email synchronously', () => {
        window.dataLayer = [];

        gtmSafePushEvent(promotionClickEvent);

        expect(window.dataLayer).toEqual([promotionClickEvent]);
    });

    test('keeps the order of events when an earlier event waits for its email hash', async () => {
        const pageReadyEvent = {
            event: GtmEventType.page_ready,
            user: { email: 'customer@example.com' },
            _clear: true,
        };
        const productListViewEvent = {
            event: GtmEventType.product_list_view,
            ecommerce: {},
            _clear: true,
        };
        window.dataLayer = [];

        gtmSafePushEvent(pageReadyEvent);
        gtmSafePushEvent(productListViewEvent);

        expect(window.dataLayer).toEqual([]);
        await vi.waitFor(() => expect(window.dataLayer).toHaveLength(2));
        expect(window.dataLayer).toEqual([
            { ...pageReadyEvent, user: { ...pageReadyEvent.user, emailHash: expect.any(String) } },
            productListViewEvent,
        ]);

        gtmSafePushEvent(promotionClickEvent);

        expect(window.dataLayer).toHaveLength(3);
    });

    test.each([{ doesReportingFail: false }, { doesReportingFail: true }])(
        'keeps sending events after a failed push when error reporting fails: $doesReportingFail',
        verifyQueueRecoversAfterFailedPush,
    );
});
