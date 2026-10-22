import { GtmMessageDetailType } from 'gtm/enums/GtmMessageDetailType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { onGtmShowMessageEventHandler } from 'gtm/handlers/onGtmShowMessageEventHandler';
import { ReactNode } from 'react';
import { ApplicationErrorsType } from 'utils/errors/applicationErrors';
import { isClient } from 'utils/isClient';
import { showMessage } from './showMessage';

type ShowErrorMessageOptions = {
    toastId?: string;
    errorType?: ApplicationErrorsType;
    fieldName?: string;
    action?: ReactNode;
};

export const showErrorMessage = (
    message: string,
    gtmMessageOrigin?: GtmMessageOriginType,
    options?: ShowErrorMessageOptions,
): void => {
    if (isClient) {
        // Use error type for deduplication instead of message text for more robust duplicate prevention
        const toastId =
            options?.toastId ??
            (options?.errorType !== undefined ? `${options.errorType}:${options.fieldName ?? 'global'}` : message);

        showMessage(message, 'error', { toastId, action: options?.action });
        onGtmShowMessageEventHandler(
            GtmMessageType.error,
            message,
            GtmMessageDetailType.flash_message,
            gtmMessageOrigin,
        );
    }
};
