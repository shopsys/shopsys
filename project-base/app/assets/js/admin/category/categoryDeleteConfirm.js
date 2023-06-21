import Register from 'framework/common/utils/Register';
import Window from 'framework/admin/utils/Window';
import Translator from 'bazinga-translator';

export default function categoryWithSeoMixDeleteConfirm ($container) {
    $container.filterAllNodes('.js-category-with-seomix-delete-confirm').click((event) => {
        // eslint-disable-next-line no-new
        new Window({
            content: Translator.trans('Do you really want to delete this category even with their SEO mixes?'),
            buttonCancel: true,
            buttonContinue: true,
            textContinue: Translator.trans('Delete category with SEO mix'),
            urlContinue: $(event.currentTarget).data('delete-url')
        });
    });

}

(new Register()).registerCallback(categoryWithSeoMixDeleteConfirm);
