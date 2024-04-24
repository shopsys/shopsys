export const isWholeElementVisible = (element: HTMLLIElement) => {
    const rect = element.getBoundingClientRect();
    const viewportHeight = window.innerHeight || document.documentElement.clientHeight;

    return rect.top >= 0 && rect.bottom <= viewportHeight;
};
