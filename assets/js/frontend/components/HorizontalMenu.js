import Timeout from 'framework/common/utils/Timeout';
import Register from 'framework/common/utils/Register';

export default class HorizontalMenu {

    constructor () {
        let _this = this;

        this.menuItems = [];
        this.$header = $('#js-sticky-menu');
        this.$openItem = null;

        $('.js-menu-horizontal-item')
            .mouseenter(function () {
                let $currentItem = $(this);

                function openCurrentItem () {
                    _this.closeOpenItem();
                    _this.openItem($currentItem);
                }

                Timeout.setTimeoutAndClearPrevious('menuHorizontalItemToggle', openCurrentItem, (_this.$openItem ? 200 : 50));
            })
            .mouseleave(function () {
                function closeOpenItem () {
                    _this.closeOpenItem();
                }

                Timeout.setTimeoutAndClearPrevious('menuHorizontalItemToggle', closeOpenItem, 500);
            });

    }

    initMobileMenu () {
        let currentId = 0;
        this.addMenuItem('Root', '', '/', -1);
        this.processMenuItems($('.js-menuHorizontal'), currentId);

        let $mobileMenuButton = $('.js-toggle-mobile-menu');

        $mobileMenuButton.click(function () {
            $('body').toggleClass('mobileMenuOpened');

            $header.css('top', '0px');
            $header.parent().css('margin-top:', '0px');
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera

            let $previousMobileMenu = $('.js-menu-mobile');

            if ($previousMobileMenu.length > 0) {
                $previousMobileMenu.each(function () {
                    $(this).remove();
                });
            }

            renderMenuByParentId(currentId, 'none');
        });
    };

    processMenuItems ($ul, parentId) {
        let _this = this;

        $ul.find('> li').each(function () {
            let $li = $(this);
            let $anchor = $li.find('> a');
            let link = $anchor.attr('href');
            let name = $.trim($anchor.text());
            let iconClass = $anchor.find('.js-menu-horizontal-icon').attr('class');
            let $children = $li.find('.js-menu-horizontal-item-container > .js-menu-horizontal-items, > .js-menu-horizontal-items');

            let itemId = _this.addMenuItem(name, iconClass, link, parentId);

            _this.processMenuItems($children, itemId);
        });
    };

    addMenuItem (name, iconClass, link, parentId) {
        let currentId = this.menuItems.length;

        this.menuItems.push({
            id: currentId,
            name: name,
            iconClass: iconClass,
            link: link,
            parentId: parentId,
            items: {}
        });

        if (parentId >= 0) {
            let parentItems = this.menuItems[parentId].items;
            let parentItemsCount = this.getObjectItemsCount(parentItems);
            parentItems[parentItemsCount] = currentId;
        }

        return currentId;
    };

    renderMenuByParentId (parentId, animation) {
        let $previousMobileMenu = $('.js-menu-mobile');
        let items = $.grep(this.menuItems, function (e) {
            return e.parentId === parentId;
        });

        let $mobileMenu = $('.js-menu-mobile-template').clone();
        $mobileMenu.addClass('js-menu-mobile');
        $mobileMenu.removeClass('js-menu-mobile-template');
        $mobileMenu.removeClass('display-none');
        $('.js-menu-mobile-template').parent().append($mobileMenu);

        renderMenuItems(items, $mobileMenu);

        if (parentId > 0) {
            renderParentAdditionalInfo($mobileMenu, parentId);
            $mobileMenu.find('.js-menu-mobile-articles').hide();
        } else {
            $mobileMenu.find('.js-menu-mobile-articles').show();
            $mobileMenu.find('.js-menu-mobile-link-back-home').show();
        }

        if ($previousMobileMenu.length > 0 && animation !== 'none') {
            this.animateToSideAndDestroyFirstElement($previousMobileMenu, $mobileMenu, animation);
        }
    };

    renderMenuItems (items, $mobileMenu) {
        for (let key in items) {
            let currentItem = items[key];
            let $mobileLink = $mobileMenu.find('.js-menu-mobile-link-template').clone();
            $mobileLink.removeClass('js-menu-mobile-link-template');
            $mobileLink.removeClass('display-none');

            let $anchor = $mobileLink.find('a');
            $anchor.attr('href', currentItem.link);
            if (currentItem.link === '#js-login') {
                $anchor.click(function (event) {
                    $('.js-toggle-mobile-menu').click(); // close menu
                    $('.js-menu-horizontal-login-button').click();

                    return false;
                });
            }
            $anchor.html('<i class="' + currentItem.iconClass + '"></i>' + currentItem.name);

            if (this.getObjectItemsCount(currentItem.items) > 0) {
                let $nextIcon = $mobileLink.find('.js-menu-mobile-next');
                $nextIcon.removeClass('display-none');
                (function (currentId) {
                    $nextIcon.click(function () {
                        renderMenuByParentId(currentId, 'right');
                    });
                })(currentItem.id);
            }

            $mobileMenu.find('> ul.js-menu-mobile-list').append($mobileLink);
        }
    };

    renderParentAdditionalInfo ($mobileMenu, parentId) {
        let $linkBack = $mobileMenu.find('.js-menu-mobile-link-back');
        $linkBack.removeClass('display-none');
        let $anchorBack = $linkBack.find('a');
        $anchorBack.click(function () {
            renderMenuByParentId(this.menuItems[parentId].parentId, 'left');
        });
        let $parentName = $('.js-menu-mobile-parent-name');
        $parentName.text(this.menuItems[parentId].name);
    };

    animateToSideAndDestroyFirstElement ($firstElement, $secondElement, side) {
        let $parent = $firstElement.parent();
        let width = $parent.width();

        let firstWidth = width;
        let secondWidth = width * -1;
        if (side === 'left') {
            firstWidth = width * -1;
            secondWidth = width;
        }

        $firstElement.css({ position: 'absolute' });
        $secondElement.hide().appendTo($parent).css({ left: firstWidth, position: 'absolute' });

        $firstElement.animate({ left: secondWidth }, 500, function () {
            $firstElement.remove();
        });
        $secondElement.show().animate({ left: 0 }, 500, function () {
            $secondElement.css({ left: null, position: null });
        });
    };

    getObjectItemsCount (object) {
        return $.map(
            object,
            function (n, i) {
                return i;
            }
        ).length;
    };

    openItem ($item) {
        $item.find('.js-menu-horizontal-item-content')
            .stop(true, false)
            .show(0);

        this.$openItem = $item;
    }

    closeOpenItem () {
        if (this.$openItem) {
            this.$openItem.find('.js-menu-horizontal-item-content')
                .stop(true, false)
                .hide(0);

            this.$openItem = null;
        }
    }

    initLinkColorSwitcher () {
        let $menuLinkHover = $('.js-linkColorSwitcher');
        $menuLinkHover
            .mouseenter(function () {
                let newStyle = 'background: ' + $(this).data('color2') + '; color: ' + $(this).data('color1') + '; border: 1px solid ' + $(this).data('color1') + ';';
                $(this).attr('style', newStyle);
            })
            .mouseleave(function () {
                let oldStyle = 'background: ' + $(this).data('color1') + '; color: ' + $(this).data('color2') + '; border: 1px solid ' + $(this).data('color1') + ';';
                $(this).attr('style', oldStyle);
            });
    }

    static init () {

        const hm = new HorizontalMenu();

        hm.initMobileMenu();
        hm.initLinkColorSwitcher();
    }
}

(new Register()).registerCallback(HorizontalMenu.init);
