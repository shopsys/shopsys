import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPromotionListViewEventType, GtmPromotionType } from 'gtm/types/events';

export const getGtmPromotionListViewEvent = (promotions: GtmPromotionType[]): GtmPromotionListViewEventType => ({
    event: GtmEventType.promotion_list_view,
    ecommerce: {
        promotions,
    },
    _clear: true,
});
