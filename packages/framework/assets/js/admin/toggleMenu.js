import $ from 'jquery';
import Register from '../common/register';

export default class ToggleMenu {

    constructor ($toggleMenu) {
        const _this = this;
        this.$items = $toggleMenu.filterAllNodes('.js-toggle-menu-item');

        this.$items.click(function (event) {
            ToggleMenu.hideAllSubmenus(_this);

            $(this).filterAllNodes('.js-toggle-menu-submenu').show();
            $(this).addClass('open');

            event.stopPropagation();
        });

        $(document).on('click', function () {
            ToggleMenu.hideAllSubmenus(_this);
        });
    }

    static hideAllSubmenus (toggleMenu) {
        toggleMenu.$items.filterAllNodes('.js-toggle-menu-submenu').hide();
        toggleMenu.$items.removeClass('open');
    };

    static init ($container) {
        $container.filterAllNodes('.js-toggle-menu').each(function () {
            // eslint-disable-next-line no-new
            new ToggleMenu($(this));
        });
    }
}

(new Register()).registerCallback(ToggleMenu.init);
