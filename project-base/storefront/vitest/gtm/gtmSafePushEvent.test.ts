import { GtmEventType } from 'gtm/enums/GtmEventType';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { describe, expect, test } from 'vitest';

describe('gtmSafePushEvent', () => {
    test('pushes events without an email synchronously', () => {
        const event = {
            event: GtmEventType.promotion_click,
            ecommerce: {},
            _clear: true,
        };
        window.dataLayer = [];

        gtmSafePushEvent(event);

        expect(window.dataLayer).toEqual([event]);
    });
});
