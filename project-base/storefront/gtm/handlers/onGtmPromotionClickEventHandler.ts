import { getGtmPromotionClickEvent } from 'gtm/factories/getGtmPromotionClickEvent';
import { GtmPromotionType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmPromotionClickEventHandler = (promotion: GtmPromotionType, destinationURL: string): void => {
    gtmSafePushEvent(getGtmPromotionClickEvent(promotion, destinationURL));
};
