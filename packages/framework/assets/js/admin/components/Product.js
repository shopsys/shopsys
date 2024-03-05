import Register from '../../common/utils/Register';

export default class Product {
    static init ($container) {
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
}

(new Register()).registerCallback(Product.init, 'Product.init');
