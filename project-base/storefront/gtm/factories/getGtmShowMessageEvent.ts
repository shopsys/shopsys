import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmMessageDetailType } from 'gtm/enums/GtmMessageDetailType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { GtmShowMessageEventType } from 'gtm/types/events';

export const getGtmShowMessageEvent = (
    type: GtmMessageType,
    message: string,
    detail: GtmMessageDetailType | string,
    origin?: GtmMessageOriginType,
): GtmShowMessageEventType => ({
    event: GtmEventType.show_message,
    eventParameters: {
        type,
        origin,
        detail,
        message,
    },
    _clear: true,
});
