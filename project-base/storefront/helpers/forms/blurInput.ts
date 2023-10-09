export const blurInput = (): void => {
    if (document.activeElement instanceof HTMLElement) {
        document.activeElement.blur();
    }
};
