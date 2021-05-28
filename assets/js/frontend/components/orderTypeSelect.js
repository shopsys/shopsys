import Register from 'framework/common/utils/Register';

export default function orderTypeSelect () {
    $('.js-order-type-select-dropdown').click(function () {
        const $container = $(this).closest('.js-order-type-select');

        if ($container.hasClass('open')) {
            $container.removeClass('open');
        } else {
            $container.addClass('open');
        }
    });
};

(new Register()).registerCallback(orderTypeSelect);
