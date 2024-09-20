import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import { addNewItemToCollection, removeItemFromCollection } from '../validation/customization/customizeCollectionBundle';
import ProductPicker from './ProductPicker';
import { escapeHtml } from '../../common/utils/escapeHtml';
import '../../common/bootstrap/tooltip';
import Translator from 'bazinga-translator';
import Window from '../utils/Window';

export default class OrderItems {

    constructor ($container) {
        const $collection = $container.filterAllNodes('#js-order-items');
        $collection.on('click', '.js-order-item-remove', (event) => this.onRemoveItemClick(event));
        $container.filterAllNodes('#js-order-item-add').on('click', (event) => this.onAddItemClick(event));

        this.refreshCount($collection);

        const _this = this;
        // eslint-disable-next-line no-new
        new ProductPicker($container.filterAllNodes('#js-order-item-add-product'), (productId, productName) => {
            _this.addProduct(productId, productName);
        });
    }

    refreshCount ($collection) {
        const $items = $collection.find('.js-order-item');
        if ($items.length === 1) {
            $items.find('.js-order-item-remove')
                .addClass('text-disabled')
                .tooltip({
                    title: Translator.trans('Order must contain at least one item'),
                    placement: 'bottom'
                });
        } else {
            $items.find('.js-order-item-remove')
                .removeClass('text-disabled')
                .tooltip('destroy');
        }
    }

    addProduct (productId, productName) {
        const $collection = $('#js-order-items');
        const _this = this;
        Ajax.ajax({
            url: $collection.data('order-product-add-url'),
            method: 'POST',
            data: {
                productId: productId
            },
            success: function (data) {
                const $data = $($.parseHTML(data));

                const $orderItem = $data.filter('.js-order-item');
                const index = $orderItem.data('index');

                $collection.append($orderItem);
                (new Register()).registerNewContent($orderItem);
                addNewItemToCollection('#js-order-items', index);

                _this.refreshCount($collection);

                // eslint-disable-next-line no-new
                new Window({
                    content: Translator.trans('Product saved in order'),
                    buttonCancel: false,
                    buttonContinue: false
                });
            },
            error: function () {
                // eslint-disable-next-line no-new
                new Window({
                    content: Translator.trans('Unable to add product'),
                    buttonCancel: false,
                    buttonContinue: false
                });
            }
        });
    }

    onRemoveItemClick (event) {
        if (!$(event.currentTarget).hasClass('text-disabled')) {
            const $item = $(event.currentTarget).closest('.js-order-item');
            const $itemNameElement = $item.find('.js-order-item-name');
            const itemName = escapeHtml($itemNameElement.val());

            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('Do you really want to remove item "<i>%itemName%</i>" from the order?', { 'itemName': itemName }),
                buttonCancel: true,
                buttonContinue: true,
                eventContinue: () => {
                    this.removeItem($item);
                }
            });
        }
        event.preventDefault();
    }

    removeItem ($item) {
        const $collection = $item.closest('#js-order-items');
        const index = $item.data('index');

        removeItemFromCollection('#js-order-items', index);
        $item.remove();

        this.refreshCount($collection);
    }

    onAddItemClick (event) {
        const $collection = $(event.currentTarget).closest('table').find('#js-order-items');

        this.addItem($collection);
        event.preventDefault();
    }

    addItem ($collection) {
        const prototype = $collection.data('prototype');
        const index = this.getNewIndex($collection);

        const item = prototype.replace(/__name__/g, index);
        const $item = $($.parseHTML(item));
        $item.data('index', index);

        $collection.append($item);
        (new Register()).registerNewContent($item);
        addNewItemToCollection('#js-order-items', index);

        this.refreshCount($collection);
    }

    getNewIndex ($collection) {
        let maxIndex = 0;
        const newItemIndex = 'new_';

        $collection.find('.js-order-item').each(function () {
            const indexStr = $(this).data('index').toString();
            if (indexStr.indexOf(newItemIndex) === 0) {
                const index = parseInt(indexStr.slice(4));
                if (index > maxIndex) {
                    maxIndex = index;
                }
            }
        });

        return newItemIndex + (maxIndex + 1);
    }

    static onPriceCalculationChange ($orderItem) {
        const setPricesManually = $orderItem.find('.js-set-prices-manually').is(':checked');

        $orderItem.find('.js-calculable-price').prop('readonly', !setPricesManually);
        $orderItem.find('.js-setting-prices-manually-warning').css('visibility', setPricesManually ? 'visible' : 'hidden');
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new OrderItems($container);

        $container.filterAllNodes('.js-order-item-any').each(function () {
            const $orderItem = $(this);

            OrderItems.onPriceCalculationChange($orderItem);
            $orderItem.find('.js-set-prices-manually').change(function () {
                OrderItems.onPriceCalculationChange($orderItem);
            });
        });
    }
}

(new Register()).registerCallback(OrderItems.init, 'OrderItems.init');
