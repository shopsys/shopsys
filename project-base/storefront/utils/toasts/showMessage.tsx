import { CopyTextBlock } from 'components/Basic/CopyTextBlock/CopyTextBlock';
import { TIDs } from 'cypress/tids';
import { toast } from 'react-toastify';
import { isWithToastAndConsoleErrorDebugging } from 'utils/errors/isWithErrorDebugging';

export const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    if (type === 'error') {
        if (isWithToastAndConsoleErrorDebugging) {
            toast.error(() => <CopyTextBlock textToCopy={message} />, {
                toastId: message,
                autoClose: false,
                closeOnClick: false,
                style: { width: '100%' },
            });
        } else {
            toast.error(() => <span dangerouslySetInnerHTML={{ __html: message }} tid={TIDs.toast_error} />, {
                toastId: message,
                closeOnClick: true,
            });
        }
    } else if (type === 'info') {
        toast.info(() => <span dangerouslySetInnerHTML={{ __html: message }} tid={TIDs.toast_info} />, {
            toastId: message,
            closeOnClick: true,
        });
    } else {
        toast.success(() => <span dangerouslySetInnerHTML={{ __html: message }} tid={TIDs.toast_success} />, {
            toastId: message,
            closeOnClick: true,
        });
    }
};
