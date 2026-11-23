import { GtmMessageDetailType } from 'gtm/enums/GtmMessageDetailType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { onGtmShowMessageEventHandler } from 'gtm/handlers/onGtmShowMessageEventHandler';
import { isClient } from 'utils/isClient';
import { showMessage } from './showMessage';

export const showWarningMessage = (message: string, gtmMessageOrigin?: GtmMessageOriginType): void => {
    if (isClient) {
        showMessage(message, 'warning');
        onGtmShowMessageEventHandler(
            GtmMessageType.information,
            message,
            GtmMessageDetailType.flash_message,
            gtmMessageOrigin,
        );
    }
};
