import $ from 'jquery';
import constant from './constant';
import Register from '../common/register';

export default class Product {
    static init () {
        const usingStockSelection = $('#product_form_displayAvailabilityGroup_usingStock input[type="radio"]');
        const $outOfStockActionSelection = $('select[name="product_form[displayAvailabilityGroup][stockGroup][outOfStockAction]"]');

        usingStockSelection.change(function () {
            Product.toggleIsUsingStock($(this).val() === '1');
        });

        $outOfStockActionSelection.change(function () {
            Product.toggleIsUsingAlternateAvailability($(this).val() === constant('\\Shopsys\\FrameworkBundle\\Model\\Product\\Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY'));
        });

        Product.toggleIsUsingStock(usingStockSelection.filter(':checked').val() === '1');
        Product.toggleIsUsingAlternateAvailability($outOfStockActionSelection.val() === constant('\\Shopsys\\FrameworkBundle\\Model\\Product\\Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY'));

        Product.initializeSideNavigation();
    }

    static initializeSideNavigation () {
        const $productDetailNavigation = $('.js-product-detail-navigation');
        const $webContent = $('.web__content');

        $('.form-group__title, .form-full__title').each(function () {
            const $title = $(this);
            const $titleClone = $title.clone();

            $titleClone.find('.js-validation-errors-list').remove();
            const $navigationItem = $('<li class="side-menu__item"><span class="side-menu__item__link"><span class="side-menu__item__text">' + $titleClone.text() + '</span></span></li>');
            $productDetailNavigation.append($navigationItem);

            $navigationItem.click(function () {
                const scrollOffsetTop = $title.offset().top - $webContent.offset().top;
                $('html, body').animate({ scrollTop: scrollOffsetTop }, 'slow');
            });
        });
    }

    static toggleIsUsingStock (isUsingStock) {
        $('.js-product-using-stock').toggle(isUsingStock);
        $('.js-product-not-using-stock').closest('.form-line').toggle(!isUsingStock);
    }

    static toggleIsUsingAlternateAvailability (isUsingStockAndAlternateAvailability) {
        $('.js-product-using-stock-and-alternate-availability').closest('.form-line').toggle(isUsingStockAndAlternateAvailability);
    }
}

(new Register()).registerCallback(Product.init);
