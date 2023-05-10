import { canUseDom } from 'helpers/misc/canUseDom';

export const blurInput = (): void => {
    if (canUseDom() && document.activeElement instanceof HTMLElement) {
        document.activeElement.blur();
    }
};
