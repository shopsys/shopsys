import 'jquery-ui/ui/widgets/mouse';
import 'jquery-ui-touch-punch';
import 'jquery-ui-nested-sortable';
import Ajax from '../../common/utils/Ajax';
import FormChangeInfo from './FormChangeInfo';
import Register from '../../common/utils/Register';
import Translator from 'bazinga-translator';
import Window from '../utils/Window';

export default class CategoryTreeSorting {

    constructor ($rootTree, $saveButton) {
        this.$rootTree = $rootTree;
        this.$saveButton = $saveButton;

        const _this = this;
        this.$rootTree.nestedSortable({
            listType: 'ul',
            handle: '.js-category-tree-item-handle',
            items: '.js-category-tree-item',
            placeholder: 'js-category-tree-placeholder form-tree__placeholder',
            toleranceElement: '> .js-category-tree-item-line',
            forcePlaceholderSize: true,
            helper: 'clone',
            opacity: 0.6,
            revert: 100,
            change: () => _this.onChange()
        });

        $saveButton.click(() => this.onSaveClick());
    }

    onChange () {
        this.$saveButton.removeClass('btn--disabled');
        FormChangeInfo.showInfo();
    }

    onSaveClick () {
        if (this.$saveButton.hasClass('btn--disabled')) {
            return;
        }

        const _this = this;
        Ajax.ajax({
            url: _this.$saveButton.data('category-save-order-url'),
            type: 'post',
            data: {
                categoriesOrderingData: _this.getCategoriesOrderingData()
            },
            success: function () {
                _this.$saveButton.addClass('btn--disabled');
                FormChangeInfo.removeInfo();
                // eslint-disable-next-line no-new
                new Window({
                    content: Translator.trans('Categories order saved.')
                });
            },
            error: function () {
                // eslint-disable-next-line no-new
                new Window({
                    content: Translator.trans('There was an error while saving. The order isn\'t saved.')
                });
            }
        });
    }

    getCategoriesOrderingData () {
        const data = this.$rootTree.nestedSortable(
            'toArray',
            {
                excludeRoot: true,
                expression: /(js-category-tree-)(\d+)/
            }
        );

        const categoriesOrderingData = [];
        $.each(data, function (key, value) {
            categoriesOrderingData.push({
                categoryId: value.id,
                parentId: value.parent_id
            });
        });

        return categoriesOrderingData;
    }

    static init ($container) {
        const $rootTree = $container.filterAllNodes('#js-category-tree-sorting > .js-category-tree-items');
        const $saveButton = $container.filterAllNodes('#js-category-tree-sorting-save-button');

        if ($rootTree.length > 0 && $saveButton.length > 0) {
            // eslint-disable-next-line no-new
            new CategoryTreeSorting($rootTree, $saveButton);
        }
    }
}

(new Register()).registerCallback(CategoryTreeSorting.init, 'CategoryTreeSorting.init');
