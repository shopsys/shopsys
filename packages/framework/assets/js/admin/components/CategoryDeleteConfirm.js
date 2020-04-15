import Register from '../../common/utils/Register';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';

export default class CategoryDeleteConfirm {

    static init ($container) {
        $container.filterAllNodes('.js-category-delete-confirm').click((event) => {
            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('Do you really want to delete this category?'),
                buttonCancel: true,
                buttonContinue: true,
                textContinue: Translator.trans('Yes'),
                urlContinue: $(event.currentTarget).data('delete-url')
            });
        });
    }

}

(new Register()).registerCallback(CategoryDeleteConfirm.init, 'CategoryDeleteConfirm.init');
