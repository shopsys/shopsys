import Ajax from '../../common/utils/Ajax';
import { escapeHtml } from '../../common/utils/escapeHtml';
import Register from '../../common/utils/Register';
import ProductPicker from './ProductPicker';
import '../../common/bootstrap/tooltip';
import ConfirmWindow from '@shopsys/administration/src/js/utils/confirmWindow';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import FormChangeInfo from './FormChangeInfo';

export default class OrderItems {
    constructor($container) {
        const $collection = $container.filterAllNodes('#js-order-items');
        $collection.on('click', '.js-order-item-remove', event => this.onRemoveItemClick(event));
        $container.filterAllNodes('#js-order-item-add').on('click', event => this.onAddItemClick(event));

        this.refreshCount($collection);
        // eslint-disable-next-line no-new
        new ProductPicker($container.filterAllNodes('#js-order-item-add-product'), (productId, productName) => {
            this.addProduct(productId, productName);
        });
    }

    refreshCount($collection) {
        const $items = $collection.find('.js-order-item');
        if ($items.length === 1) {
            $items
                .find('.js-order-item-remove')
                .addClass('text-disabled')
                .tooltip({
                    title: Translator.trans('Order must contain at least one item'),
                    placement: 'bottom',
                });
        } else {
            $items.find('.js-order-item-remove').removeClass('text-disabled');
            // .tooltip('destroy');
        }
    }

    addProduct(productId, _productName) {
        const $collection = $('#js-order-items');
        Ajax.ajax({
            url: $collection.data('order-product-add-url'),
            method: 'POST',
            data: {
                productId: productId,
            },
            success: data => {
                const $data = $($.parseHTML(data));

                const $orderItem = $data.filter('.js-order-item');

                $collection.append($orderItem);
                new Register().registerNewContent($orderItem);
                FormChangeInfo.showInfo();

                this.refreshCount($collection);

                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Product saved in order'),
                });
            },
            error: () => {
                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Unable to add product'),
                });
            },
        });
    }

    onRemoveItemClick(event) {
        if (!$(event.currentTarget).hasClass('text-disabled')) {
            const $item = $(event.currentTarget).closest('.js-order-item');
            const $itemNameElement = $item.find('.js-order-item-name');
            const itemName = escapeHtml($itemNameElement.val());

            ConfirmWindow.show({
                content: Translator.trans('Do you really want to remove item "<i>%itemName%</i>" from the order?', {
                    itemName: itemName,
                }),
                continueEvent: () => {
                    this.removeItem($item);
                },
            });
        }
        event.preventDefault();
    }

    removeItem($item) {
        const $collection = $item.closest('#js-order-items');

        $item.remove();

        this.refreshCount($collection);
    }

    onAddItemClick(event) {
        const $collection = $(event.currentTarget).closest('table').find('#js-order-items');

        this.addItem($collection);
        event.preventDefault();
    }

    addItem($collection) {
        const prototype = $collection.data('prototype');
        const index = this.getNewIndex($collection);

        const item = prototype.replace(/__name__/g, index);
        const $item = $($.parseHTML(item));
        $item.data('index', index);

        $collection.append($item);
        new Register().registerNewContent($item);
        FormChangeInfo.showInfo();

        this.refreshCount($collection);
    }

    getNewIndex($collection) {
        let maxIndex = 0;
        const newItemIndex = 'new_';

        $collection.find('.js-order-item').each(function () {
            const indexStr = $(this).data('index').toString();
            if (indexStr.indexOf(newItemIndex) === 0) {
                const index = parseInt(indexStr.slice(4), 10);
                if (index > maxIndex) {
                    maxIndex = index;
                }
            }
        });

        return newItemIndex + (maxIndex + 1);
    }

    static onPriceCalculationChange($orderItem) {
        const setPricesManually = $orderItem.find('.js-set-prices-manually').is(':checked');

        $orderItem.find('.js-calculable-price').prop('readonly', !setPricesManually);
        $orderItem
            .find('.js-setting-prices-manually-warning')
            .css('visibility', setPricesManually ? 'visible' : 'hidden');
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new OrderItems($container);

        $container.filterAllNodes('.js-order-item-any').each(function () {
            const $orderItem = $(this);

            OrderItems.onPriceCalculationChange($orderItem);
            $orderItem.find('.js-set-prices-manually').change(() => {
                OrderItems.onPriceCalculationChange($orderItem);
            });
        });
    }
}

new Register().registerCallback(OrderItems.init, 'OrderItems.init');
