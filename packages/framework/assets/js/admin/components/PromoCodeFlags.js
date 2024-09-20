import Register from '../../common/utils/Register';
import { addNewItemToCollection, removeItemFromCollection } from '../validation/customization/customizeCollectionBundle';

export default class PromoCodeFlags {
    static init ($container) {
        const $flagsItemAdd = $container.filterAllNodes('.js-flags-item-add');
        const $flags = $container.filterAllNodes('.js-flags');

        $flags.on('click', '.js-flags-item-remove', function (event) {
            const $collection = $(this).closest('.js-flags');

            const $item = $(this).closest('.js-flags-item');
            const index = $item.data('index');
            removeItemFromCollection('.js-flags', index);
            $item.remove();

            PromoCodeFlags.refreshCount($collection);
            event.preventDefault();
        });

        $flagsItemAdd.on('click', function () {
            const $collection = $(this).closest('.js-form-group').find('.js-flags');
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

            addNewItemToCollection('.js-flags', index);
            PromoCodeFlags.refreshCount($collection);

            return false;
        });

        PromoCodeFlags.refreshCount($flags);
    }

    static refreshCount ($collection) {
        if ($collection.find('.js-flags-item').length === 0) {
            $collection.find('.js-flags-empty-item').show();
        } else {
            $collection.find('.js-flags-empty-item').hide();
        }
    }
}

(new Register()).registerCallback(PromoCodeFlags.init, 'PromoCodeFlags.init');
