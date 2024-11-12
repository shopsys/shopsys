const isTextSelected = () => {
    const selection = window.getSelection();
    return selection && selection.toString().length > 0;
};

export const disableClickWhenTextSelected = (e: React.MouseEvent<HTMLAnchorElement>) => {
    if (isTextSelected()) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
    return true;
};
