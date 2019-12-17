import $ from 'jquery';
import Register from '../common/register';

export default class SideMenu {

    constructor ($sideMenu) {
        this.$sideMenu = $sideMenu;
        this.$items = this.$sideMenu.filterAllNodes('.js-side-menu-item');

        this.$items.click(function () {
            $(this).filterAllNodes('.js-side-menu-submenu').show();
            $(this).addClass('open');
        });

        if (this.$sideMenu.hasClass('js-side-menu-close-after-mouseleave')) {
            this.closeMenusAfterMouseleave(500);
        }
    };

    closeMenusAfterMouseleave (timoutMilliseconds) {
        let timeoutHandle;
        this.$sideMenu.hover(
            function () { clearTimeout(timeoutHandle); },
            function () { timeoutHandle = setTimeout(self.closeMenus, timoutMilliseconds); }
        );
    };

    closeMenus () {
        this.$items.filterAllNodes('.js-side-menu-submenu').hide();
        this.$items.removeClass('open');
    };

    static init ($container) {
        $container.filterAllNodes('.js-side-menu').each(function () {
            // eslint-disable-next-line no-new
            new SideMenu($(this));
        });
    }
};

(new Register()).registerCallback(SideMenu.init);
