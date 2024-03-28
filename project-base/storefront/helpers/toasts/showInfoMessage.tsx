import { showMessage } from './showMessage';
import { GtmMessageDetailType } from 'gtm/enums/GtmMessageDetailType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { onGtmShowMessageEventHandler } from 'gtm/handlers/onGtmShowMessageEventHandler';
import { isClient } from 'helpers/isClient';

export const showInfoMessage = (message: string, gtmMessageOrigin?: GtmMessageOriginType): void => {
    if (isClient) {
        showMessage(message, 'info');
        onGtmShowMessageEventHandler(
            GtmMessageType.information,
            message,
            GtmMessageDetailType.flash_message,
            gtmMessageOrigin,
        );
    }
};
