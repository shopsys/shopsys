import $ from 'jquery';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

export default class PromoCodeLimits {
    static init($container) {
        const $collection = $container.filterAllNodes('.js-limits');

        $collection.on('click', '.js-limits-item-remove', function (event) {
            const $collection = $(this).closest('.js-limits');

            const $item = $(this).closest('.js-limits-item');
            FormChangeInfo.showInfo();
            $item.remove();

            PromoCodeLimits.refreshCount($collection);
            event.preventDefault();
        });

        $container.filterAllNodes('.js-limits-item-add').on('click', function () {
            const $collection = $(this).closest('.js-form-group').find('.js-limits');
            const index = $collection.data('index');

            const prototype = $collection.data('prototype');
            const item = prototype.replace(/__name__label__/g, index).replace(/__name__/g, index);
            const $item = $($.parseHTML(item));
            $item.data('index', index);

            $collection.data('index', index + 1);

            $collection.append($item);
            new Register().registerNewContent($item);

            FormChangeInfo.showInfo();
            PromoCodeLimits.refreshCount($collection);

            return false;
        });

        PromoCodeLimits.refreshCount($collection);
    }

    static refreshCount($collection) {
        if ($collection.find('.js-limits-item').length === 0) {
            $collection.find('.js-limits-empty-item').show();
        } else {
            $collection.find('.js-limits-empty-item').hide();
        }
    }
}

new Register().registerCallback(PromoCodeLimits.init, 'PromoCodeLimits.init');
