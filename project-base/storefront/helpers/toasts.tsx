import { onGtmShowMessageEventHandler } from 'gtm/helpers/eventHandlers';
import { toast } from 'react-toastify';
import { GtmMessageDetailType, GtmMessageOriginType, GtmMessageType } from 'gtm/types/enums';

const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    if (type === 'error') {
        toast.error(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid="toast-error" />, {
            toastId: message,
        });
    }
    if (type === 'info') {
        toast.info(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid="toast-info" />, {
            toastId: message,
        });
    }
    if (type === 'success') {
        toast.success(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid="toast-success" />, {
            toastId: message,
        });
    }
};

export const showErrorMessage = (message: string, gtmMessageOrigin?: GtmMessageOriginType): void => {
    showMessage(message, 'error');
    onGtmShowMessageEventHandler(GtmMessageType.error, message, GtmMessageDetailType.flash_message, gtmMessageOrigin);
};

export const showInfoMessage = (message: string, gtmMessageOrigin?: GtmMessageOriginType): void => {
    showMessage(message, 'info');
    onGtmShowMessageEventHandler(
        GtmMessageType.information,
        message,
        GtmMessageDetailType.flash_message,
        gtmMessageOrigin,
    );
};

export const showSuccessMessage = (message: string): void => {
    showMessage(message, 'success');
};
