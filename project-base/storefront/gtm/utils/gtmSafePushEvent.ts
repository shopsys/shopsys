import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmEventInterface } from 'gtm/types/events';
import { isClient } from 'utils/isClient';

export const gtmSafePushEvent = (event: GtmEventInterface<GtmEventType, unknown>): void => {
    if (isClient) {
        window.dataLayer = window.dataLayer ?? [];
        window.dataLayer.push(event);
    }
};
