import Register from '../../common/utils/Register';
import { addNewItemToCollection, removeItemFromCollection } from '../validation/customization/customizeCollectionBundle';

export default class Parameters {

    static init ($container) {
        const $collection = $container.filterAllNodes('.js-parameters');

        $collection.on('click', '.js-parameters-item-remove', function (event) {
            const $collection = $(this).closest('.js-parameters');

            const $item = $(this).closest('.js-parameters-item');
            const index = $item.data('index');
            removeItemFromCollection('.js-parameters', index);
            $item.remove();

            Parameters.refreshCount($collection);
            event.preventDefault();
        });

        $container.filterAllNodes('.js-parameters-item-add').on('click', function () {
            const $collection = $(this).closest('.js-form-group').find('.js-parameters');
            const index = $collection.data('index');

            const prototype = $collection.data('prototype');
            const item = prototype
                .replace(/__name__label__/g, index)
                .replace(/__name__/g, index);
            const $item = $($.parseHTML(item));
            $item.data('index', index);

            $collection.data('index', index + 1);

            $collection.append($item);
            (new Register()).registerNewContent($item);

            addNewItemToCollection('.js-parameters', index);
            Parameters.refreshCount($collection);

            return false;
        });

        Parameters.refreshCount($collection);
    }

    static refreshCount ($collection) {
        if ($collection.find('.js-parameters-item').length === 0) {
            $collection.find('.js-parameters-empty-item').show();
        } else {
            $collection.find('.js-parameters-empty-item').hide();
        }
    }
}

(new Register()).registerCallback(Parameters.init, 'Parameters.init');
