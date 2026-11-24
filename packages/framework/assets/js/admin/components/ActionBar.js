import Register from '../../common/utils/Register';

export default class ActionBar {
    static init($container) {
        const actionBar = $container.filterAllNodes('.js-action-bar-bottom').get(0);

        if (actionBar === undefined) return;

        // the page keeps room for the fixed action bar, whose height depends on its content and the screen width
        new ResizeObserver(() => ActionBar.updateHeightProperty(actionBar)).observe(actionBar);
    }

    static updateHeightProperty(actionBar) {
        document.documentElement.style.setProperty('--admin-action-bar-height', `${actionBar.offsetHeight}px`);
    }
}

new Register().registerCallback(ActionBar.init, 'ActionBar.init');
