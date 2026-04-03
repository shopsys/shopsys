import { GtmMessageDetailType } from 'gtm/enums/GtmMessageDetailType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { onGtmShowMessageEventHandler } from 'gtm/handlers/onGtmShowMessageEventHandler';
import { ApplicationErrorsType } from 'utils/errors/applicationErrors';
import { isClient } from 'utils/isClient';
import { showMessage } from './showMessage';

type ShowErrorMessageOptions = {
    errorType?: ApplicationErrorsType;
    fieldName?: string;
};

export const showErrorMessage = (
    message: string,
    gtmMessageOrigin?: GtmMessageOriginType,
    options?: ShowErrorMessageOptions,
): void => {
    if (isClient) {
        // Use error type for deduplication instead of message text for more robust duplicate prevention
        const toastId =
            options?.errorType !== undefined ? `${options.errorType}:${options.fieldName ?? 'global'}` : message;

        showMessage(message, 'error', { toastId });
        onGtmShowMessageEventHandler(
            GtmMessageType.error,
            message,
            GtmMessageDetailType.flash_message,
            gtmMessageOrigin,
        );
    }
};
