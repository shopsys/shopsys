import { CopyTextBlock } from 'components/Basic/CopyTextBlock/CopyTextBlock';
import { TIDs } from 'cypress/tids';
import { toast } from 'react-toastify';
import { useSessionStore } from 'store/useSessionStore';
import { isWithToastAndConsoleErrorDebugging } from 'utils/errors/isWithErrorDebugging';

export const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    const { restoreStoredFocus } = useSessionStore.getState();

    if (type === 'error') {
        if (isWithToastAndConsoleErrorDebugging) {
            toast.error(() => <CopyTextBlock textToCopy={message} />, {
                toastId: message,
                autoClose: false,
                closeOnClick: false,
                style: { width: '100%' },
                onClose: () => restoreStoredFocus(),
            });
        } else {
            toast.error(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_error} />, {
                toastId: message,
                closeOnClick: true,
                onClose: () => restoreStoredFocus(),
            });
        }
    } else if (type === 'info') {
        toast.info(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_info} />, {
            toastId: message,
            closeOnClick: true,
            onClose: () => restoreStoredFocus(),
        });
    } else {
        toast.success(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_success} />, {
            toastId: message,
            closeOnClick: true,
            onClose: () => restoreStoredFocus(),
        });
    }
};
