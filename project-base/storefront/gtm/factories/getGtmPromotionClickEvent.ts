import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPromotionClickEventType, GtmPromotionType } from 'gtm/types/events';

export const getGtmPromotionClickEvent = (
    promotion: GtmPromotionType,
    destinationURL: string,
): GtmPromotionClickEventType => ({
    event: GtmEventType.promotion_click,
    ecommerce: {
        ...promotion,
        destinationURL,
    },
    _clear: true,
});
