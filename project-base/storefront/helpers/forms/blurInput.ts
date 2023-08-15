import { canUseDom } from 'helpers/canUseDom';

export const blurInput = (): void => {
    if (canUseDom() && document.activeElement instanceof HTMLElement) {
        document.activeElement.blur();
    }
};
