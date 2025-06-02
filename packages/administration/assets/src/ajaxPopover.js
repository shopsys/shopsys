let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"][data-content-url]'));

popoverTriggerList.map(function (popoverTriggerEl) {
    popoverTriggerEl.addEventListener('shown.bs.popover', function () {
        fetch(this.getAttribute('data-content-url'))
            .then((response) => response.text())
            .then((data) => {
                const popover = bootstrap.Popover.getInstance(popoverTriggerEl);

                popover.tip.querySelector('.popover-body').innerHTML = data;
                popover.update();
            });
    });
});
