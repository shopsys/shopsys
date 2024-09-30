import Register from '../../common/utils/Register';
import { addNewItemToCollection, removeItemFromCollection } from '../validation/customization/customizeCollectionBundle';

export default class TransportPriceWithWeightLimitCollection {
    static init () {
        const $transportPriceItemAdd = $('.js-transport-prices-item-add');
        const $transportPricesCollection = $('.js-transport-prices');

        $transportPriceItemAdd.off('click');
        $transportPricesCollection.off('click', '.js-transport-prices-item-remove');

        $transportPricesCollection.on('click', '.js-transport-prices-item-remove', function (event) {
            const $item = $(this).closest('.js-transport-prices-item');
            const index = $item.data('index');
            removeItemFromCollection('.js-transport-prices', index);
            $item.remove();

            event.preventDefault();
        });

        $transportPriceItemAdd.on('click', function () {
            const $collection = $(this).closest('.js-transport-prices-form-group').find('.js-transport-prices');
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

            addNewItemToCollection('.js-transport-prices', index);

            event.preventDefault();
            return false;
        });
    }
}

(new Register()).registerCallback(TransportPriceWithWeightLimitCollection.init, 'TransportPriceWithWeightLimitCollection.init');
