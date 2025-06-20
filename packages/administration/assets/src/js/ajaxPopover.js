import { Popover } from '@tabler/core';

let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"][data-content-url]'));
let contentCache = {};

$('body').on('click', function (e) {
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

popoverTriggerList.map(function (popoverTriggerEl) {
    popoverTriggerEl.addEventListener('shown.bs.popover', function () {
        const contentUrl = this.getAttribute('data-content-url');

        if (contentCache[contentUrl]) {
            updatePopoverContent(contentCache[contentUrl], popoverTriggerEl);
        } else {
            fetch(contentUrl)
                .then((response) => response.text())
                .then((data) => {
                    contentCache[contentUrl] = data;
                    updatePopoverContent(data, popoverTriggerEl);
                });
        }
    });
});

const updatePopoverContent = function (content, popoverTriggerEl) {
    const popover = Popover.getInstance(popoverTriggerEl);
    if (popover) {
        popover.tip.querySelector('.popover-body').innerHTML = content;
        popover.update();
    }
}
