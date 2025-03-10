export const isTextSelected = () => {
    const selection = window.getSelection();

    return selection && selection.toString().length > 0;
};
