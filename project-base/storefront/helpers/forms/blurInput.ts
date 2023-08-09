import { canUseDom } from 'helpers/DOM/canUseDom';

export const blurInput = (): void => {
    if (canUseDom() && document.activeElement instanceof HTMLElement) {
        document.activeElement.blur();
    }
};
