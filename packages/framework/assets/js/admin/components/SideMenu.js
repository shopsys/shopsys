import Register from '../../common/utils/Register';

export default class SideMenu {

    constructor ($sideMenu) {
        this.$webPanel = $('.js-web-panel');
        this.$sideMenuMobile = $('.js-side-menu-mobile');
        this.$sideMenuOverlay = $('.js-side-menu-overlay');
        this.$sideMenuCollapseButton = $('.js-side-menu-collapse-button');
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

        // show left menu on mobile devices
        this.$sideMenuMobile.click(event => {
            if (!$('body').hasClass('active-menu')) {
                $('body').addClass('active-menu');
                this.$webPanel.addClass('open');
            } else {
                $('body').removeClass('active-menu');
                this.$webPanel.removeClass('open');
            }
        });

        // close left menu on overlay click
        this.$sideMenuOverlay.click(event => {
            $('body').removeClass('active-menu');
            this.$webPanel.removeClass('open');
        });

        // show/hide left menu on desktop
        this.$sideMenuCollapseButton.click(event => {
            $('body').toggleClass('menu-collapsed');
            if (!this.$webPanel.hasClass('active-menu')) {
                this.$webPanel.removeClass('open');
            } else {
                this.$webPanel.addClass('open');
            }
        });
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
