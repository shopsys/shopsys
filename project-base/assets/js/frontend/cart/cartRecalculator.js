import $ from 'jquery';
import Ajax from '../../copyFromFw/ajax';
import Timeout from '../../copyFromFw/components/timeout';
import Register from '../../copyFromFw/register';
import constant from '../constant';

export default class CartRecalculator {

    constructor ($container) {
        const _this = this;

        // reload content after delay when clicking +/-
        $container.filterAllNodes('.js-cart-item .js-spinbox-plus, .js-cart-item .js-spinbox-minus').click(
            function (event) {
                _this.reloadWithDelay(1000, _this);
                event.preventDefault();
            }
        );

        // reload content after delay after leaving input or pressing ENTER
        // but only if value was changed
        $container.filterAllNodes('.js-cart-item .js-spinbox-input')
            .change(function () {
                $(this).blur(function () {
                    _this.reloadWithDelay(1000, _this);
                });
            })
            .keydown(function (event) {
                if (event.keyCode === Shopsys.keyCodes.ENTER) {
                    _this.reloadWithDelay(0, _this);
                    event.preventDefault();
                }
            });
    }

    reload () {
        const formData = $('.js-cart-form').serializeArray();
        formData.push({
            name: constant('\\App\\Controller\\Front\\CartController::RECALCULATE_ONLY_PARAMETER_NAME'),
            value: 1
        });

        Ajax.ajax({
            overlayDelay: 0, // show loader immediately to avoid clicking during AJAX request
            loaderElement: '.js-main-content',
            url: $('.js-cart-form').attr('action'),
            type: 'post',
            data: formData,
            dataType: 'html',
            success: function (html) {
                const $html = $($.parseHTML(html));

                const $mainContent = $html.find('.js-main-content');
                const $cartBox = $html.find('#js-cart-box');

                $('.js-main-content').replaceWith($mainContent);
                $('#js-cart-box').replaceWith($cartBox);

                (new Register()).registerNewContent($mainContent);
                (new Register()).registerNewContent($cartBox);
            }
        });
    }

    reloadWithDelay (delay, cartRecalculator) {
        Timeout.setTimeoutAndClearPrevious(
            'cartRecalculator',
            function () {
                cartRecalculator.reload();
            },
            delay
        );
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new CartRecalculator($container);
    };

}

(new Register()).registerCallback(CartRecalculator.init);
