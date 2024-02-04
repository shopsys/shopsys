import { isWithErrorDebugging } from './errors/isWithErrorDebugging';
import { isClient } from './isClient';
import CopyTextBlock from 'components/Basic/CopyTextBlock/CopyTextBlock';
import { onGtmShowMessageEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmMessageDetailType, GtmMessageOriginType, GtmMessageType } from 'gtm/types/enums';
import { toast } from 'react-toastify';

const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    if (type === 'error') {
        if (isWithErrorDebugging) {
            toast.error(() => <CopyTextBlock textToCopy={message} />, {
                toastId: message,
                autoClose: false,
                closeOnClick: false,
                style: { width: '100%' },
            });
        } else {
            toast.error(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid="toast-error" />, {
                toastId: message,
            });
        }
    } else if (type === 'info') {
        toast.info(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid="toast-info" />, {
            toastId: message,
        });
    } else {
        toast.success(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid="toast-success" />, {
            toastId: message,
        });
    }
};

export const showErrorMessage = (message: string, gtmMessageOrigin?: GtmMessageOriginType): void => {
    if (isClient) {
        showMessage(message, 'error');
        onGtmShowMessageEventHandler(
            GtmMessageType.error,
            message,
            GtmMessageDetailType.flash_message,
            gtmMessageOrigin,
        );
    }
};

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

export const showSuccessMessage = (message: string): void => {
    if (isClient) {
        showMessage(message, 'success');
    }
};
