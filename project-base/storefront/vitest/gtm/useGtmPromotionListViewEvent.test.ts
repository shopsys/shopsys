import { renderHook } from '@testing-library/react';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { useGtmPromotionListViewEvent } from 'gtm/utils/pageReadyEvents/useGtmPromotionListViewEvent';
import { describe, expect, test, vi } from 'vitest';

const { gtmSafePushEventMock } = vi.hoisted(() => ({
    gtmSafePushEventMock: vi.fn(),
}));

vi.mock('gtm/context/GtmProvider', () => ({
    useGtmContext: () => ({ didPageReadyRun: true, isScriptLoaded: true }),
}));

vi.mock('gtm/utils/gtmSafePushEvent', () => ({
    gtmSafePushEvent: gtmSafePushEventMock,
}));

const promotionA = {
    promotionId: 'homepage_banners_slider',
    promotionName: 'Homepage - carousel',
    creativeName: 'Banner A',
    creativeSlot: 1,
};
const promotionB = { ...promotionA, creativeName: 'Banner B', creativeSlot: 2 };
const promotionC = { ...promotionA, creativeName: 'Banner C', creativeSlot: 3 };

describe('useGtmPromotionListViewEvent', () => {
    test('sends only newly added promotions when the list expands', () => {
        const { rerender } = renderHook(({ promotions }) => useGtmPromotionListViewEvent(promotions), {
            initialProps: { promotions: [promotionA, promotionB] },
        });

        rerender({ promotions: [promotionA, promotionB, promotionC] });

        expect(gtmSafePushEventMock).toHaveBeenCalledTimes(2);
        expect(gtmSafePushEventMock).toHaveBeenNthCalledWith(1, {
            event: GtmEventType.promotion_list_view,
            ecommerce: { promotions: [promotionA, promotionB] },
            _clear: true,
        });
        expect(gtmSafePushEventMock).toHaveBeenNthCalledWith(2, {
            event: GtmEventType.promotion_list_view,
            ecommerce: { promotions: [promotionC] },
            _clear: true,
        });
    });
});
