import Register from '../../common/utils/Register';
import {
    addNewItemToCollection,
    removeItemFromCollection
} from '../validation/customization/customizeCollectionBundle';

export default class HreflangSetting {
    static init ($container) {
        const $collectionItemAddButton = $container.filterAllNodes('.js-hreflang-setting-item-add');
        const $collection = $container.filterAllNodes('.js-hreflang-setting');

        $collection.on('click', '.js-hreflang-setting-item-remove', function (event) {
            const $collection = $(this).closest('.js-hreflang-setting');

            const $item = $(this).closest('.js-hreflang-setting-item');
            const index = $item.data('index');
            removeItemFromCollection('.js-hreflang-setting', index);
            $item.remove();

            HreflangSetting.refreshCount($collection);
            event.preventDefault();
        });

        $collectionItemAddButton.on('click', function () {
            const $collection = $(this).closest('.js-form-group').find('.js-hreflang-setting');
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

            addNewItemToCollection('.js-hreflang-setting', index);
            HreflangSetting.refreshCount($collection);

            return false;
        });

        HreflangSetting.refreshCount($collection);
    }

    static refreshCount ($collection) {
        if ($collection.find('.js-hreflang-setting-item').length === 0) {
            $collection.find('.js-hreflang-setting-empty-item').show();
        } else {
            $collection.find('.js-hreflang-setting-empty-item').hide();
        }
    }
}

(new Register()).registerCallback(HreflangSetting.init, 'HreflangSetting.init');
