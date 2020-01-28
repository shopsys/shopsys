import Register from '../../common/utils/Register';

export default class SideMenu {

    constructor ($sideMenu) {
        this.$elementPanelClass = '.js-web-panel';

        this.$sideMenu = $sideMenu;
        this.$items = this.$sideMenu.filterAllNodes('.js-side-menu-item');

        this.$items.click(function () {
            if ($(this).hasClass('open')) {
                $(this).parents(this.$elementPanelClass).removeClass('open');
            } else {
                $(this).parents(this.$elementPanelClass).addClass('open');
            }
            $(this).filterAllNodes('.js-side-menu-submenu').toggle();
            $(this).toggleClass('open');
        });

        if (this.$sideMenu.hasClass('js-side-menu-close-after-mouseleave')) {
            this.closeMenusAfterMouseleave(500);
        }
    }

    closeMenusAfterMouseleave (timoutMilliseconds) {
        let timeoutHandle;
        this.$sideMenu.hover(
            function () { clearTimeout(timeoutHandle); },
            function () { timeoutHandle = setTimeout(self.closeMenus, timoutMilliseconds); }
        );
    }

    closeMenus () {
        this.$sideMenu.removeClass('open');
        this.$items.filterAllNodes('.js-side-menu-submenu').hide();
        this.$items.removeClass('open');
    }

    static init ($container) {
        $container.filterAllNodes(this.$elementPanelClass).each(function () {
            // eslint-disable-next-line no-new
            new SideMenu($(this));
        });
    }
}

(new Register()).registerCallback(SideMenu.init, 'SideMenu.init');
