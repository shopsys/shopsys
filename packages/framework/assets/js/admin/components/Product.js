import Register from '../../common/utils/Register';

export default class Product {
    static init ($container) {
        const usingStockSelection = $('#product_form_displayAvailabilityGroup_usingStock input[type="radio"]');
        const $outOfStockActionSelection = $('select[name="product_form[displayAvailabilityGroup][stockGroup][outOfStockAction]"]');

        usingStockSelection.change(function () {
            Product.toggleIsUsingStock($(this).val() === '1');
        });

        const alternateAvailability = 'setAlternateAvailability';
        $outOfStockActionSelection.change(function () {
            Product.toggleIsUsingAlternateAvailability($(this).val() === alternateAvailability);
        });

        Product.toggleIsUsingStock(usingStockSelection.filter(':checked').val() === '1');
        Product.toggleIsUsingAlternateAvailability($outOfStockActionSelection.val() === alternateAvailability);

        Product.initializeSideNavigation($container);
    }

    static initializeSideNavigation ($container) {
        const $productDetailNavigation = $container.find('.js-product-detail-navigation');
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

(new Register()).registerCallback(Product.init, 'Product.init');
