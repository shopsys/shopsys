import Register from '../../common/utils/Register';
import { addNewItemToCollection, removeItemFromCollection } from '../validation/customization/customizeCollectionBundle';

export default class OpeningHoursCollection {
    static init ($container) {
        const $openingHoursCollection = $container.filterAllNodes('.js-opening-hours');

        $openingHoursCollection.on('click', '.js-opening-hours-item-remove', function (event) {
            const $item = $(this).closest('.js-opening-hours-item');
            const index = $item.data('index');
            removeItemFromCollection('.js-opening-hours', index);
            $item.remove();

            event.preventDefault();
        });

        $container.on('click', '.js-opening-hours-item-add', function (event) {
            const $collection = $(this).closest('.js-opening-hours-form-group').find('.js-opening-hours');
            const index = $collection.data('index');

            const prototype = $collection.data('prototype');
            const item = prototype
                .replace(/__name__label__/g, index)
                .replace(/__name__/g, index);
            const $item = $($.parseHTML(item));
            $item.data('index', index);

            $collection.data('index', index + 1);

            $collection.append($item);

            addNewItemToCollection('.js-opening-hours', index);

            event.preventDefault();
        });
    }
}

(new Register()).registerCallback(OpeningHoursCollection.init, 'OpeningHoursCollection.init');
