import Register from 'framework/common/utils/Register';

export default function orderTypeSelect ($container) {
    const toggleButton = $container.filterAllNodes('.js-order-type-select-dropdown');

    toggleButton.click(function () {
        const $container = $(this).closest('.js-order-type-select');
        console.log('trigered');

        if ($container.hasClass('open')) {
            $container.removeClass('open');
        } else {
            $container.addClass('open');
        }
    });
};

(new Register()).registerCallback(orderTypeSelect);
