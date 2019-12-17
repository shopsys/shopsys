import $ from 'jquery';
import Register from '../common/register';
import { addNewItemToCollection, removeItemFromCollection } from '../common/validation/customizeBundle';

export default class Parameters {

    static init () {

        $('.js-parameters-item-add').off('click');
        $('.js-parameters').off('click', '.js-parameters-item-remove');

        $('.js-parameters').on('click', '.js-parameters-item-remove', function (event) {
            const $collection = $(this).closest('.js-parameters');

            const $item = $(this).closest('.js-parameters-item');
            const index = $item.data('index');
            removeItemFromCollection('#product_form_parameters', index);
            $item.remove();

            Parameters.refreshCount($collection);
            event.preventDefault();
        });

        $('.js-parameters-item-add').on('click', function () {
            const $collection = $('.js-parameters');
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

            addNewItemToCollection('#product_form_parameters', index);
            Parameters.refreshCount($collection);

            return false;
        });

        Parameters.refreshCount($('.js-parameters'));
    }

    static refreshCount ($collection) {
        if ($collection.find('.js-parameters-item').length === 0) {
            $collection.find('.js-parameters-empty-item').show();
        } else {
            $collection.find('.js-parameters-empty-item').hide();
        }
    };
}

(new Register()).registerCallback(Parameters.init);
