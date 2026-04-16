import ConfirmWindow from '@shopsys/administration/src/js/utils/confirmWindow';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import { Tooltip } from '@tabler/core';
import Translator from 'bazinga-translator';
import Ajax from '../../common/utils/Ajax';
import { escapeHtml } from '../../common/utils/escapeHtml';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';
import ProductPicker from './ProductPicker';

export default class OrderItems {
    static textDisabledClass = 'link-disabled';

    constructor($container) {
        this.$container = $container;
        this.$card = $container.filterAllNodes('.js-order-items-card');
        this.$previewSection = this.$card.find('.js-order-items-preview');
        this.$editSections = this.$card.find('.js-order-items-edit');
        this.$previewOnlyControls = this.$card.find('.js-order-items-preview-only');
        this.$editOnlyControls = $container.filterAllNodes('.js-order-items-edit-only');

        const $collection = $container.filterAllNodes('#js-order-items');
        this.$collection = $collection;

        $collection.on('click', '.js-order-item-remove', event => this.onRemoveItemClick(event));
        $container.filterAllNodes('#js-order-item-add').on('click', event => this.onAddItemClick(event));
        this.$card.on('click', '.js-order-items-switch-to-edit', event => this.onSwitchToEditClick(event));
        this.$card.on('click', '.js-order-items-add-item-preview', event => this.onAddItemPreviewClick(event));
        this.$card.on('click', '.js-order-items-add-product-preview', event => this.onAddProductPreviewClick(event));
        this.$card.on('click', '.js-order-item-remove-preview', event => this.onRemoveItemPreviewClick(event));

        this.tooltip = null;
        this.refreshCount($collection);
        this.initializeMode();

        // eslint-disable-next-line no-new
        new ProductPicker($container.filterAllNodes('#js-order-item-add-product'), (productId, productName) => {
            this.addProduct(productId, productName);
        });
    }

    initializeMode() {
        if (this.$card.data('initial-mode') === 'edit') {
            this.switchToEditMode();
        }
    }

    onSwitchToEditClick(event) {
        event.preventDefault();
        this.switchToEditMode();
    }

    onAddItemPreviewClick(event) {
        event.preventDefault();
        this.switchToEditMode();
        this.onAddItemClick();
    }

    onAddProductPreviewClick(event) {
        event.preventDefault();
        this.switchToEditMode();
        this.$container.filterAllNodes('#js-order-item-add-product').trigger('click');
    }

    onRemoveItemPreviewClick(event) {
        event.preventDefault();
        this.switchToEditMode();

        const index = String($(event.currentTarget).data('index'));
        const $removeButton = this.$collection.find(`.js-order-item[data-index="${index}"] .js-order-item-remove`);

        if ($removeButton.length) {
            $removeButton.trigger('click');
        }
    }

    switchToEditMode() {
        this.$previewSection.addClass('d-none');
        this.$previewOnlyControls.addClass('d-none');
        this.$editSections.removeClass('d-none');
        this.$editOnlyControls.removeClass('d-none');
    }

    refreshCount($collection) {
        const $items = $collection.find('.js-order-item');

        if ($items.length === 1) {
            const $orderItemRemoveButton = $items.find('.js-order-item-remove');

            $orderItemRemoveButton.addClass(OrderItems.textDisabledClass);

            this.tooltip = new Tooltip($orderItemRemoveButton, {
                title: Translator.trans('Order must contain at least one item'),
            });
        } else {
            $items.find('.js-order-item-remove').removeClass(OrderItems.textDisabledClass);

            if (this.tooltip) {
                this.tooltip.dispose();
                this.tooltip = null;
            }
        }
    }

    addProduct(productId, _productName) {
        const $collection = this.$collection;
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
        if (!$(event.currentTarget).hasClass(OrderItems.textDisabledClass)) {
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

        FormChangeInfo.showInfo();
        this.refreshCount($collection);
    }

    onAddItemClick() {
        const $collection = this.$collection;

        this.addItem($collection);
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
        $orderItem.find('.js-setting-prices-manually-warning').css('display', setPricesManually ? 'block' : 'none');
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
