import { Popover } from '@tabler/core';
import Register from 'framework/common/utils/Register';

$('body').on('click', e => {
    if ($(e.target).closest('.popover').length > 0) {
        return;
    }

    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(trigger => {
        const popover = Popover.getInstance(trigger);
        if (popover) {
            popover.hide();
        }
    });
});

function initAjaxPopovers($container) {
    const popoverTriggerList = [].slice.call($container.filterAllNodes('[data-bs-toggle="popover"][data-content-url]'));
    const contentCache = {};

    popoverTriggerList.forEach(popoverTriggerEl => {
        popoverTriggerEl.addEventListener('shown.bs.popover', function () {
            const contentUrl = this.getAttribute('data-content-url');

            if (contentCache[contentUrl]) {
                updatePopoverContent(contentCache[contentUrl], popoverTriggerEl);
            } else {
                fetch(contentUrl)
                    .then(response => response.text())
                    .then(data => {
                        contentCache[contentUrl] = data;
                        updatePopoverContent(data, popoverTriggerEl);
                    });
            }
        });
    });

    const updatePopoverContent = (content, popoverTriggerEl) => {
        const popover = Popover.getInstance(popoverTriggerEl);
        if (popover) {
            popover.tip.querySelector('.popover-body').innerHTML = content;
            popover.update();
        }
    };
}

new Register().registerCallback(initAjaxPopovers, 'initAjaxPopovers');
