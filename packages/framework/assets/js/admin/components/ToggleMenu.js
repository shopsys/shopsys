import Register from '../../common/utils/Register';

export default class ToggleMenu {

    constructor ($toggleMenu) {
        const _this = this;
        this.$items = $toggleMenu.filterAllNodes('.js-toggle-menu-item');

        this.$items.click(function (event) {
            const isOpened = $(this).hasClass('open');

            ToggleMenu.hideAllSubmenus(_this);

            if (!isOpened) {
                $(this).filterAllNodes('.js-toggle-menu-submenu').show();
                $(this).addClass('open');
            }

            event.stopPropagation();
        });

        $(document).on('click', function () {
            ToggleMenu.hideAllSubmenus(_this);
        });

        this.$items.find('.js-toggle-menu-submenu').on('click', function (event) {
            event.stopPropagation();
        });
    }

    static hideAllSubmenus (toggleMenu) {
        toggleMenu.$items.filterAllNodes('.js-toggle-menu-submenu').hide();
        toggleMenu.$items.removeClass('open');
    }

    static init ($container) {
        $container.filterAllNodes('.js-toggle-menu').each(function () {
            // eslint-disable-next-line no-new
            new ToggleMenu($(this));
        });
    }
}

(new Register()).registerCallback(ToggleMenu.init, 'ToggleMenu.init');
