import $ from 'jquery';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Sortable from 'sortablejs';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

window.ProductsPickerInstances = {};

export default class ProductsPicker {
    constructor($productsPicker) {
        this.instanceId = Object.keys(window.ProductsPickerInstances).length;
        window.ProductsPickerInstances[this.instanceId] = this;

        this.$productsPicker = $productsPicker;
        this.$header = $productsPicker.find('.js-products-picker-header');
        this.$addButton = $productsPicker.find('.js-products-picker-button-add');
        this.$itemsContainer = $productsPicker.find('.js-products-picker-items');
        this.productItems = [];
        this.mainProductId = $productsPicker.data('products-picker-main-product-id');

        const _this = this;
        this.$addButton.click(() => _this.openProductsPickerWindow());
        this.$itemsContainer.find('.js-products-picker-item').each(function () {
            _this.initItem($(this));
        });
        this.$itemsContainer.each((_index, element) => {
            Sortable.create(element, {
                handle: '.js-products-picker-item-handle',
                draggable: '.js-products-picker-item',
                animation: 150,
                onUpdate: () => this.updateOrdering(),
            });
        });
    }

    openProductsPickerWindow() {
        const url = this.$productsPicker.data('products-picker-url').replace('__js_instance_id__', this.instanceId);

        const iframeContent = `<iframe src="${url}" style="width: 100%; height: 800px; border: none;"></iframe>`;

        // eslint-disable-next-line no-new
        new ModalWindow({
            content: iframeContent,
            title: Translator.trans('Assign products'),
            size: 'xl',
            buttons: [{ text: Translator.trans('Finish assigning') }],
        });

        return false;
    }

    initItem($item) {
        this.productItems.push($item);
        $item.find('.js-products-picker-item-button-delete').click(event => {
            event.preventDefault();
            this.removeItem($item);
        });
    }

    removeItem($item) {
        const productId = $item.find('.js-products-picker-item-input:first').val();
        delete this.productItems[this.findProductItemIndex(productId)];
        const productItem = this.findProductItemIndex(productId);
        const newProductItems = [];
        for (const key in this.productItems) {
            if (this.productItems[key] !== productItem) {
                newProductItems.push(this.productItems[key]);
            }
        }
        this.productItems = newProductItems;

        $item.remove();
        this.reIndex();
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    findProductItemIndex(productId) {
        for (const key in this.productItems) {
            if (this.productItems[key].find('.js-products-picker-item-input:first').val() === productId.toString()) {
                return key;
            }
        }

        return null;
    }

    reIndex() {
        this.$itemsContainer.find('.js-products-picker-item-input').each((index, element) => {
            const name = $(element).attr('name');
            const newName = `${name.substr(0, name.lastIndexOf('[') + 1) + index}]`;
            $(element).attr('name', newName);
        });
    }

    updateHeader() {
        this.$header.toggle(this.productItems.length !== 0);
    }

    updateOrdering() {
        this.reIndex();
        FormChangeInfo.showInfo();
    }

    isMainProduct(productId) {
        return this.mainProductId !== '' && this.mainProductId === productId;
    }

    removeItemByProductId(productId) {
        const $item = this.findProductItemByProductId(productId);
        this.removeItem($item);
    }

    findProductItemByProductId(productId) {
        return this.productItems[this.findProductItemIndex(productId)];
    }

    hasProduct(productId) {
        return this.findProductItemIndex(productId) !== null;
    }

    addProduct(productId, productName, productImageHtml) {
        const nextIndex = this.$itemsContainer.find('.js-products-picker-item').length;
        const itemHtml = this.$productsPicker.data('products-picker-prototype').replace(/__name__/g, nextIndex);
        const $item = $($.parseHTML(itemHtml));
        $item.find('.js-products-picker-item-product-name').text(productName);
        $item.find('.js-products-picker-item-product-image').html(productImageHtml);
        $item.find('.js-products-picker-item-input').val(productId);
        this.$itemsContainer.append($item);
        this.initItem($item);
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    static init($container) {
        $container.filterAllNodes('.js-products-picker').each(function () {
            // eslint-disable-next-line no-new
            new ProductsPicker($(this));
        });
    }
}

new Register().registerCallback(ProductsPicker.init, 'ProductsPicker.init');
