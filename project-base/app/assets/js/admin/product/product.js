import Register from 'framework/common/utils/Register';

export default class ProductVideos {

    static init ($container) {
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

(new Register()).registerCallback(ProductVideos.init, 'ProductVideos.init');
