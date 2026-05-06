import { Tooltip } from '@tabler/core';
import PinIcon from 'icons/tabler/pin.svg';
import PinnedIcon from 'icons/tabler/pinned-filled.svg';
import Sortable from 'sortablejs';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

const ICON_ATTRIBUTES = 'class="icon icon-sm" fill="currentColor" height="20" width="20"';

export default class PinnedMenuItem {
    static init($container) {
        $container.filterAllNodes('[data-js-nav-pinned]').each(function () {
            const $pin = $(this);

            $pin.on('click', e => {
                e.preventDefault();
                e.stopPropagation();

                Ajax.ajax({
                    url: $pin.data('toggle-url'),
                    type: 'POST',
                    data: { routeName: $pin.data('route-name') },
                    dataType: 'json',
                    success: data => {
                        PinnedMenuItem.rerenderPinnedSection(data.pinnedSectionHtml);
                        PinnedMenuItem.updateSourcePinButton($pin.data('route-name'), data.pinned);
                    },
                });
            });
        });

        $container.filterAllNodes('[data-js-nav-pinned-sortable]').each(function () {
            const reorderUrl = $(this).data('reorder-url');

            Sortable.create(this, {
                animation: 150,
                handle: '[data-js-nav-pinned-drag-handle]',
                draggable: '.nav-item',
                ghostClass: 'sortable-ghost',
                onEnd: () => {
                    const orderedPaths = [];

                    $(this)
                        .find('li[data-route-name]')
                        .each(function () {
                            orderedPaths.push($(this).data('route-name'));
                        });

                    Ajax.ajax({
                        url: reorderUrl,
                        type: 'POST',
                        data: { orderedPaths: orderedPaths },
                        dataType: 'json',
                    });
                },
            });
        });
    }

    static rerenderPinnedSection(pinnedSectionHtml) {
        const $liveSidebar = $('.sidebar.navbar-nav');
        const $liveSortable = $liveSidebar.find('[data-js-nav-pinned-sortable]').first();

        if ($liveSortable.length > 0) {
            const $liveSectionLi = $liveSortable.closest('li.nav-item');
            const $liveDividerLi = $liveSectionLi.next('li.nav-item');

            PinnedMenuItem.disposeTooltipsIn($liveSectionLi);

            $liveSectionLi.remove();
            $liveDividerLi.remove();
        }

        const $newNodes = $($.parseHTML(pinnedSectionHtml));

        if ($newNodes.length === 0) {
            return;
        }

        $liveSidebar.prepend($newNodes);

        PinnedMenuItem.markCurrentPinnedItem($newNodes);

        new Register().registerNewContent($newNodes);
    }

    static markCurrentPinnedItem($scope) {
        const currentPath = window.location.pathname;

        $scope.find('a.nav-link').each(function () {
            if (new URL(this.href, window.location.origin).pathname === currentPath) {
                $(this).addClass('show');
            }
        });
    }

    static updateSourcePinButton(routeName, pinned) {
        const selector = `[data-js-nav-pinned][data-route-name="${routeName}"]`;
        const $button = $('.sidebar.navbar-nav')
            .find(selector)
            .filter(function () {
                return $(this).closest('[data-js-nav-pinned-sortable]').length === 0;
            })
            .first();

        if ($button.length === 0) {
            return;
        }

        const title = pinned ? $button.data('title-pinned') : $button.data('title-unpinned');
        const iconSvg = pinned ? PinnedIcon : PinIcon;

        Tooltip.getInstance($button[0])?.dispose();

        $button
            .attr('data-pinned', pinned ? 'true' : 'false')
            .attr('title', title)
            .html(iconSvg.replace('<svg', `<svg ${ICON_ATTRIBUTES}`));

        new Tooltip($button[0]);
    }

    static disposeTooltipsIn($root) {
        $root.filterAllNodes('[data-bs-toggle="tooltip"]').each(function () {
            Tooltip.getInstance(this)?.dispose();
        });
    }
}

// eslint-disable-next-line no-new
new Register().registerCallback(PinnedMenuItem.init, 'PinnedMenuItem.init');
