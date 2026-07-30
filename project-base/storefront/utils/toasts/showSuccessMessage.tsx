import { isClient } from 'utils/isClient';
import { ShowMessageOptions, showMessage } from './showMessage';

export const showSuccessMessage = (message: string, options?: ShowMessageOptions): void => {
    if (isClient) {
        showMessage(message, 'success', options);
    }
};
