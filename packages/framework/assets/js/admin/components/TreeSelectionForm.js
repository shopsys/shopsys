import Register from '../../common/utils/Register';
import TreeSelectionFormItem from './TreeSelectionFormItem';

export default class TreeSelectionForm {
    constructor($tree) {
        $tree.find('> .js-tree-selection-form-children-container > .js-tree-selection-form-item').each(function () {
            // eslint-disable-next-line no-new
            new TreeSelectionFormItem($(this), null);
        });
    }

    static init($container) {
        $container.filterAllNodes('.js-tree-selection-form').each(function () {
            // eslint-disable-next-line no-new
            new TreeSelectionForm($(this));
        });
    }
}

new Register().registerCallback(TreeSelectionForm.init, 'TreeSelectionForm.init');
