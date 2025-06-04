import { CopyTextBlock } from 'components/Basic/CopyTextBlock/CopyTextBlock';
import { TIDs } from 'cypress/tids';
import { toast } from 'react-toastify';
import { isWithToastAndConsoleErrorDebugging } from 'utils/errors/isWithErrorDebugging';

const focusOnToast = () => {
    // added delay to ensure the toast is rendered
    setTimeout(() => {
        const toastElement = document.querySelector('.Toastify__toast-container') as HTMLElement | null;
        toastElement?.setAttribute('tabindex', '0');
        toastElement?.focus();
    }, 100);
};

export const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    if (type === 'error') {
        if (isWithToastAndConsoleErrorDebugging) {
            toast.error(() => <CopyTextBlock textToCopy={message} />, {
                toastId: message,
                autoClose: false,
                closeOnClick: false,
                style: { width: '100%' },
                onOpen: focusOnToast,
            });
        } else {
            toast.error(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_error} />, {
                toastId: message,
                closeOnClick: true,
                onOpen: focusOnToast,
            });
        }
    } else if (type === 'info') {
        toast.info(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_info} />, {
            toastId: message,
            closeOnClick: true,
            onOpen: focusOnToast,
        });
    } else {
        toast.success(() => <span dangerouslySetInnerHTML={{ __html: message }} data-tid={TIDs.toast_success} />, {
            toastId: message,
            closeOnClick: true,
            onOpen: focusOnToast,
        });
    }
};
