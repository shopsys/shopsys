import CategoryTreeFormItem from './CategoryTreeFormItem';
import Register from '../../common/utils/Register';

export default class CategoryTreeForm {

    constructor ($tree) {
        $tree.find('> .js-category-tree-form-children-container > .js-category-tree-form-item').each(function () {
            // eslint-disable-next-line no-new
            new CategoryTreeFormItem($(this), null);
        });
    }

    static init ($container) {
        $container.filterAllNodes('.js-category-tree-form').each(function () {
            // eslint-disable-next-line no-new
            new CategoryTreeForm($(this));
        });
    }
}

(new Register()).registerCallback(CategoryTreeForm.init, 'CategoryTreeForm.init');
