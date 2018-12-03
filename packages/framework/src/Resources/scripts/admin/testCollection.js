(function ($) {

    Shopsys = window.Shopsys || {};

    Shopsys.register.registerCallback(function ($container) {
        $container.filterAllNodes('.js-test-collection').each(function () {
            var $collection = $(this);
            var $buttonAdd = $($.parseHTML('<div class="form-line form-line--no-top-border"><a class="js-test-collection-add btn btn--plus"><i class="btn__icon">+</i>Add another item</a></div>'));
            $buttonAdd.find('.js-test-collection-add').click(function () {
                var prototype = $collection.data('prototype');
                var index = $collection.data('new-index');
                var $item = $($.parseHTML(prototype.replace(/__name__/g, index)));

                $collection.append($item);

                Shopsys.register.registerNewContent($item);
                Shopsys.validation.addNewItemToCollection($collection, index);
                $collection.data('new-index', index + 1);
            });
            $collection.data('new-index', $collection.children().length);
            $collection.after($buttonAdd);
        });

        $container.filterAllNodes('.js-test-collection-item').each(function () {
            var $item = $(this);
            var $buttonRemove = $($.parseHTML('<a class="js-test-collection-remove"><i class="svg in-icon margin-left-15 svg-circle-cross"></i></a>'));
            $buttonRemove.click(function () {
                var $collection = $item.closest('.js-test-collection');
                var index = $item.attr('id').replace(/.*_/, '');
                $item.closest('.form-line').remove();
                Shopsys.validation.removeItemFromCollection($collection, index);
            });
            $item.after($buttonRemove);
        });
    });

})(jQuery);
