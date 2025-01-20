import Register from '../../common/utils/Register';

export default class Product {
    static init ($container) {
        Product.initializeSideNavigation($container);
        Product.initProductVideos($container);
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

    static initProductVideos ($container) {
        $container.filterAllNodes('.js-videos-collection').on('click', '.js-remove-row', function () {
            $(this).parent().parent().remove();
        });

        $container.filterAllNodes('.js-videos-collection-add-row').on('click', function (event) {
            const $collection = $(this).closest('.js-form-group').find('.js-videos-collection');
            let index = $collection.data('index');
            index++;
            let prototype = $collection.data('prototype');
            let item = prototype
                .replace(/__name__label__/g, index)
                .replace(/__name__/g, index);

            let $item = $($.parseHTML(item));

            $item.data('index', index);
            $collection.data('index', index);
            $collection.append($item);
        });
    }
}

(new Register()).registerCallback(Product.init, 'Product.init');
