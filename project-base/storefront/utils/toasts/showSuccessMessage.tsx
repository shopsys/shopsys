import { isClient } from 'utils/isClient';
import { showMessage } from './showMessage';

export const showSuccessMessage = (message: string): void => {
    if (isClient) {
        showMessage(message, 'success');
    }
};
