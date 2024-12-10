export const isTextSelected = () => {
    const selection = window.getSelection();
    return selection && selection.toString().length > 0;
};

export const disableClickWhenTextSelected = (e: React.MouseEvent<HTMLAnchorElement>) => {
    if (isTextSelected()) {
        preventClick(e);
    }
    return true;
};

export const preventClick = (event: React.MouseEvent<HTMLAnchorElement>) => {
    event.preventDefault();
    event.stopPropagation();
    return false;
};
