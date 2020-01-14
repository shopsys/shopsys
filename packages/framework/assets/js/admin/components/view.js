export function getBottomOffset () {
    const windowFixedBarHeight = $('.js-window-fixed-bar').height() || 0;
    const symfonyBarHeight = $('.sf-toolbar').height() || 0;

    return windowFixedBarHeight + symfonyBarHeight;
}
