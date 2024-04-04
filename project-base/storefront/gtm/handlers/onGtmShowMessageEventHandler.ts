import { GtmMessageDetailType } from 'gtm/enums/GtmMessageDetailType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { getGtmShowMessageEvent } from 'gtm/factories/getGtmShowMessageEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmShowMessageEventHandler = (
    type: GtmMessageType,
    message: string,
    detail: GtmMessageDetailType | string,
    origin?: GtmMessageOriginType,
): void => {
    gtmSafePushEvent(getGtmShowMessageEvent(type, message, detail, origin));
};
