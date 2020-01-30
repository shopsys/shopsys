(function ($) {

    Shopsys = Shopsys || {};
    Shopsys.categoryTree = Shopsys.categoryTree || {};
    Shopsys.categoryTree.rootTrees = [];
    Shopsys.categoryTree.saveButton = null;

    Shopsys.register.registerCallback(function ($container) {
        var rootTrees = $container.filterAllNodes('#js-category-tree-sorting .js-category-tree-root-items');
        var $saveButton = $container.filterAllNodes('#js-category-tree-sorting-save-button');

        if (rootTrees.length > 0 && $saveButton.length > 0) {
            $.each(rootTrees, function (key, rootTree) {
                var $rootTree = $(rootTree);
                var protectRoot = $rootTree.hasClass('js-protect-root');

                $rootTree.nestedSortable({
                    listType: 'ul',
                    handle: '.js-category-tree-item-handle',
                    items: '.js-category-tree-item',
                    placeholder: 'js-category-tree-placeholder form-tree__placeholder',
                    toleranceElement: '> .js-category-tree-item-line',
                    forcePlaceholderSize: true,
                    helper: 'clone',
                    opacity: 0.6,
                    revert: 100,
                    change: Shopsys.categoryTree.onChange,
                    protectRoot: protectRoot
                });

                Shopsys.categoryTree.rootTrees.push(rootTree);
            });

            $saveButton.click(Shopsys.categoryTree.onSaveClick);
            Shopsys.categoryTree.saveButton = $saveButton;
        }
    });

    Shopsys.categoryTree.onChange = function () {
        Shopsys.categoryTree.saveButton.removeClass('btn--disabled');
        Shopsys.formChangeInfo.showInfo();
    };

    Shopsys.categoryTree.onSaveClick = function () {
        if (Shopsys.categoryTree.saveButton.hasClass('btn--disabled')) {
            return;
        }

        Shopsys.ajax({
            url: Shopsys.categoryTree.saveButton.data('category-save-order-url'),
            type: 'post',
            data: {
                categoriesOrderingData: Shopsys.categoryTree.getCategoriesOrderingData()
            },
            success: function () {
                Shopsys.categoryTree.saveButton.addClass('btn--disabled');
                Shopsys.formChangeInfo.removeInfo();
                Shopsys.window({
                    content: Shopsys.translator.trans('Order saved.')
                });
            },
            error: function () {
                Shopsys.window({
                    content: Shopsys.translator.trans('There was an error while saving. The order isn\'t saved.')
                });
            }
        });
    };

    Shopsys.categoryTree.getCategoriesOrderingData = function () {
        var dataFromAllTrees = [];
        $.each(Shopsys.categoryTree.rootTrees, function (key, rootTree) {
            var data = $(rootTree).nestedSortable(
                'toArray',
                {
                    excludeRoot: true,
                    expression: /(js-category-tree-)(\d+)/
                }
            );

            dataFromAllTrees = dataFromAllTrees.concat(data);
        });

        var categoriesOrderingData = [];
        $.each(dataFromAllTrees, function (key, value) {
            categoriesOrderingData.push({
                categoryId: value.item_id,
                parentId: value.parent_id
            });
        });

        return categoriesOrderingData;
    };

})(jQuery);
