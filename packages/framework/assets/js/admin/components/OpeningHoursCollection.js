import Register from '../../common/utils/Register';
import { addNewItemToCollection, removeItemFromCollection } from '../validation/customization/customizeCollectionBundle';

export default class OpeningHoursCollection {
    static init () {
        const $openingHoursItemAdd = $('.js-opening-hours-item-add');
        const $openingHoursCollection = $('.js-opening-hours');

        $openingHoursItemAdd.off('click');
        $openingHoursCollection.off('click', '.js-opening-hours-item-remove');

        $openingHoursCollection.on('click', '.js-opening-hours-item-remove', function (event) {
            const $item = $(this).closest('.js-opening-hours-item');
            const index = $item.data('index');
            removeItemFromCollection('.js-opening-hours', index);
            $item.remove();

            event.preventDefault();
        });

        $openingHoursItemAdd.on('click', function () {
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
            (new Register()).registerNewContent($item);

            addNewItemToCollection('.js-opening-hours', index);

            event.preventDefault();
            return false;
        });
    }
}

(new Register()).registerCallback(OpeningHoursCollection.init, 'OpeningHoursCollection.init');
