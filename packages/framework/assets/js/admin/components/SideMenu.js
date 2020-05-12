import Register from '../../common/utils/Register';
const jsSideMenuSubmenuSelector = '.js-side-menu-submenu';
const jsSideMenuItemSelector = '.js-side-menu-item';
const jsSideMenuSelector = '.js-side-menu';

export default class SideMenu {

    constructor ($sideMenu) {

        this.$webPanel = $('.js-web-panel');
        this.$sideMenuMobile = $('.js-side-menu-mobile');
        this.$sideMenuOverlay = $('.js-side-menu-overlay');
        this.$sideMenuCollapseButton = $('.js-side-menu-collapse-button');
        this.$sideMenuItemLink = $(jsSideMenuItemSelector + ' .side-menu__submenu__item__link');
        this.$sideMenu = $sideMenu;
        this.$items = this.$sideMenu.filterAllNodes(jsSideMenuItemSelector);

        this.$sideMenu.find(jsSideMenuItemSelector + '.open ul').removeClass('hidden');

        $(jsSideMenuItemSelector + ' a.side-menu__submenu__item__link').click(event => {
            event.stopPropagation();
        });

        this.$items.click(event => {
            if ($(event.currentTarget).hasClass('open')) {
                $(event.currentTarget).filterAllNodes(jsSideMenuSubmenuSelector).addClass('hidden');
                $(event.currentTarget).removeClass('open');
                this.$webPanel.removeClass('open');
                this.$webPanel.filterAllNodes(jsSideMenuSubmenuSelector).addClass('hidden');
                this.$webPanel.filterAllNodes(jsSideMenuItemSelector).removeClass('open');
            } else {
                $(event.currentTarget).filterAllNodes(jsSideMenuSubmenuSelector).removeClass('hidden');
                $(event.currentTarget).addClass('open');
                this.$webPanel.addClass('open');
            }
        });

        if (this.$sideMenu.hasClass('js-side-menu-close-after-mouseleave')) {
            this.closeMenusAfterMouseleave(500);
        }

        // show left menu on mobile devices
        this.$sideMenuMobile.click(() => {
            if (!$('body').hasClass('active-menu')) {
                $('body').addClass('active-menu');
                this.$webPanel.addClass('open');
            } else {
                $('body').removeClass('active-menu');
                this.$webPanel.removeClass('open');
            }
        });

        // close left menu on overlay click
        this.$sideMenuOverlay.click(() => {
            $('body').removeClass('active-menu');
            this.$webPanel.removeClass('open');
        });

        // show/hide left menu on desktop
        this.$sideMenuCollapseButton.click(() => {
            $('body').toggleClass('menu-collapsed');
            if (!this.$webPanel.hasClass('active-menu')) {
                this.$webPanel.removeClass('open');
                this.$sideMenu.find(jsSideMenuItemSelector).removeClass('open');
                this.$sideMenu.find(jsSideMenuItemSelector + ' ul').addClass('hidden');
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
        this.$webPanel.filterAllNodes(jsSideMenuSubmenuSelector).addClass('hidden');
        this.$items.removeClass('open');
        this.$webPanel.removeClass('open');
    }

    static init ($container) {
        $container.filterAllNodes(jsSideMenuSelector).each(function () {
            // eslint-disable-next-line no-new
            new SideMenu($(this));
        });
    }
}

(new Register()).registerCallback(SideMenu.init, 'SideMenu.init');
