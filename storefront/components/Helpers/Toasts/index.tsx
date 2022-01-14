import { toast } from 'react-toastify';

const showMessage = (message: string, type: 'info' | 'error' | 'success'): void => {
    if (type === 'error') {
        toast.error(() => <span dangerouslySetInnerHTML={{ __html: message }}></span>);
    }
    if (type === 'info') {
        toast.info(() => <span dangerouslySetInnerHTML={{ __html: message }}></span>);
    }
    if (type === 'success') {
        toast.success(() => <span dangerouslySetInnerHTML={{ __html: message }}></span>);
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
