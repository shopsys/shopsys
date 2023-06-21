import Register from 'framework/common/utils/Register';

export default class ProductVideos {

    static init () {
        $('.js-videos-collection').on('click', '.js-remove-row', function () {
            $(this).parent().parent().remove();
        });

        $('.js-videos-collection-add-row').on('click', function (event) {
            let $collection = $('.js-videos-collection');
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
