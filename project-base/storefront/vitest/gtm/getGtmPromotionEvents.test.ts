import { GtmEventType } from 'gtm/enums/GtmEventType';
import { getGtmPromotionClickEvent } from 'gtm/factories/getGtmPromotionClickEvent';
import { getGtmPromotionListViewEvent } from 'gtm/factories/getGtmPromotionListViewEvent';
import { describe, expect, test } from 'vitest';

const promotion = {
    promotionId: 'homepage_banners_slider',
    promotionName: 'Homepage - carousel',
    creativeName: 'Christmas sale',
    creativeSlot: 42,
};

describe('GTM promotion events', () => {
    test('creates a promotion list view event', () => {
        expect(getGtmPromotionListViewEvent([promotion])).toEqual({
            event: GtmEventType.promotion_list_view,
            ecommerce: {
                promotions: [promotion],
            },
            _clear: true,
        });
    });

    test('creates a promotion click event with its destination URL', () => {
        expect(getGtmPromotionClickEvent(promotion, '/christmas-sale/')).toEqual({
            event: GtmEventType.promotion_click,
            ecommerce: {
                ...promotion,
                destinationURL: '/christmas-sale/',
            },
            _clear: true,
        });
    });
});
