import { showMessage } from './showMessage';
import { isClient } from 'utils/isClient';

export const showSuccessMessage = (message: string): void => {
    if (isClient) {
        showMessage(message, 'success');
    }
};
