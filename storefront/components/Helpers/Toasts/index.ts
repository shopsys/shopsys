import { toast } from 'react-toastify';

const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    if (type === 'error') {
        toast.error(message);
    } else if (type === 'info') {
        toast.info(message);
    } else if (type === 'success') {
        toast.success(message);
    }
};

export const showErrorMessage = (message: string): void => {
    showMessage(message, 'error');
};
export const showInfoMessage = (message: string): void => {
    showMessage(message, 'info');
};
export const showSuccessMessage = (message: string): void => {
    showMessage(message, 'success');
};
