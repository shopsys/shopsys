import { Popover } from '@tabler/core';

let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"][data-content-url]'));
let contentCache = {};

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
