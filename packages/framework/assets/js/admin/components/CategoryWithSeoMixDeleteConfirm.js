import Register from '../../common/utils/Register';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';

export default class CategoryWithSeoMixDeleteConfirm {
    static init ($container) {
        $container.filterAllNodes('.js-category-with-seomix-delete-confirm').click((event) => {
            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('Do you really want to remove this category even with their SEO mixes?'),
                buttonCancel: true,
                buttonContinue: true,
                textContinue: Translator.trans('Delete category with SEO mix'),
                urlContinue: $(event.currentTarget).data('delete-url')
            });
        });
    }
}

(new Register()).registerCallback(CategoryWithSeoMixDeleteConfirm.init, 'CategoryWithSeoMixDeleteConfirm.init');
