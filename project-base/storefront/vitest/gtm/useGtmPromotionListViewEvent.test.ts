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
    creativeSlot: '8e6a3287-e691-457a-baf4-6ca6a67e10ac',
};
const promotionB = { ...promotionA, creativeName: 'Banner B', creativeSlot: 'fe42f12b-33c0-4b65-bd77-a45b371a1260' };
const promotionC = { ...promotionA, creativeName: 'Banner C', creativeSlot: '984ee93c-df92-41ee-b862-214b4a17e235' };

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
