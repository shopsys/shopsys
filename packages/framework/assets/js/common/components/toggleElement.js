import Register from '../register';

export default class ToggleElement {

    static show ($container) {
        const $content = $container.find('.js-toggle-content');

        $container.trigger('showContent.toggleElement');

        $content.slideDown('fast', function () {
            $content.removeClass('display-none');
        });
    };

    static hide ($container) {
        const $content = $container.find('.js-toggle-content');

        $container.trigger('hideContent.toggleElement');

        $content.slideUp('fast', function () {
            $content.addClass('display-none');
        });
    };

    static toggle () {
        const $container = $(this).closest('.js-toggle-container');
        const $content = $container.find('.js-toggle-content');
        if ($content.hasClass('display-none')) {
            ToggleElement.show($container);
        } else {
            ToggleElement.hide($container);
        }
    }

    static init ($container) {
        $container.filterAllNodes('.js-toggle-container .js-toggle-button')
            .bind('click', ToggleElement.toggle);
    }
}

(new Register()).registerCallback(ToggleElement.init);
