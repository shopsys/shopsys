import Register from '../../common/utils/Register';

export default class Product {
    static init($container) {
        Product.initializeSideNavigation($container);
        Product.initProductVideos($container);
    }

    static initializeSideNavigation($container) {
        const $productDetailNavigation = $container.find('.toc .nav');
        const $topOffset = $('.page-header').offset().top;

        $('#product_form h3').each(function () {
            const $title = $(this);
            const $titleClone = $title.clone();

            const $navigationItem = $(
                `<span class="nav-link cursor-pointer"><span class="nav-link-title">${$titleClone.text()}</span></span>`,
            );
            $productDetailNavigation.append($navigationItem);

            $navigationItem.click(() => {
                const scrollOffsetTop = $title.offset().top - $topOffset;
                $('html, body').animate({ scrollTop: scrollOffsetTop }, 'slow');
            });
        });
    }

    static initProductVideos($container) {
        $container.filterAllNodes('.js-videos-collection').on('click', '.js-remove-row', function () {
            $(this).parent().parent().remove();
        });

        $container.filterAllNodes('.js-videos-collection-add-row').on('click', function (_event) {
            const $collection = $(this).closest('.js-form-group').find('.js-videos-collection');
            let index = $collection.data('index');
            index++;
            const prototype = $collection.data('prototype');
            const item = prototype.replace(/__name__label__/g, index).replace(/__name__/g, index);

            const $item = $($.parseHTML(item));

            $item.data('index', index);
            $collection.data('index', index);
            $collection.append($item);
        });
    }
}

new Register().registerCallback(Product.init, 'Product.init');
