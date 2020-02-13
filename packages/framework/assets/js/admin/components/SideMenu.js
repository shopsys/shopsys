import Register from '../../common/utils/Register';

export default class SideMenu {

    constructor ($sideMenu) {
        this.$webPanel = $('.js-web-panel');
        this.$sideMenu = $sideMenu;
        this.$items = this.$sideMenu.filterAllNodes('.js-side-menu-item');
        const _this = this;

        this.$items.click(event => {
            if ($(event.currentTarget).hasClass('open')) {
                $(event.currentTarget).filterAllNodes('.js-side-menu-submenu').addClass('hidden');
                $(event.currentTarget).removeClass('open');
                _this.$webPanel.removeClass('open');
                _this.$webPanel.filterAllNodes('.js-side-menu-submenu').addClass('hidden');
                _this.$webPanel.filterAllNodes('.js-side-menu-item').removeClass('open');
            } else {
                $(event.currentTarget).filterAllNodes('.js-side-menu-submenu').removeClass('hidden');
                $(event.currentTarget).addClass('open');
                _this.$webPanel.addClass('open');
            }
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
        this.$webPanel.filterAllNodes('.js-side-menu-submenu').addClass('hidden');
        this.$items.removeClass('open');
        this.$webPanel.removeClass('open');
    }

    static init ($container) {
        $container.filterAllNodes('.js-side-menu').each(function () {
            // eslint-disable-next-line no-new
            new SideMenu($(this));
        });
    }
}

(new Register()).registerCallback(SideMenu.init, 'SideMenu.init');
