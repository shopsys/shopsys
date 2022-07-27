import { toast } from 'react-toastify';
import { GtmMessageOriginType } from 'types/gtm';
import { getGtmMessageEvent } from 'utils/Gtm/EventFactories';
import { gtmSafePushEvent } from 'utils/Gtm/Gtm';

const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    if (type === 'error') {
        toast.error(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid={'toast-error'} />, {
            toastId: message,
        });
    }
    if (type === 'info') {
        toast.info(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid={'toast-info'} />, {
            toastId: message,
        });
    }
    if (type === 'success') {
        toast.success(() => <span dangerouslySetInnerHTML={{ __html: message }} data-testid={'toast-success'} />, {
            toastId: message,
        });
    }
};

export const showErrorMessage = (message: string, origin?: GtmMessageOriginType): void => {
    showMessage(message, 'error');
    const event = getGtmMessageEvent('error', message, 'flash message', origin);
    gtmSafePushEvent(event);
};
export const showInfoMessage = (message: string, origin?: GtmMessageOriginType): void => {
    showMessage(message, 'info');
    const event = getGtmMessageEvent('information', message, 'flash message', origin);
    gtmSafePushEvent(event);
};
export const showSuccessMessage = (message: string): void => {
    showMessage(message, 'success');
};
