import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';

window.ProductPickerInstances = {};

export default class ProductPicker {
    constructor($pickerButton, onSelectProductCallback) {
        this.instanceId = Object.keys(window.ProductPickerInstances).length;
        window.ProductPickerInstances[this.instanceId] = this;
        this.$pickerButton = $pickerButton;
        this.onSelectProductCallback = onSelectProductCallback;
        this.$container = this.$pickerButton.closest('.js-product-picker-container');
        this.$input = this.$container.find('.js-product-picker-input');
        this.$label = this.$container.find('.js-product-picker-label');
        this.$removeButton = this.$container.find('.js-product-picker-remove-button');

        this.$pickerButton.click(event => this.makePicker(event));
        this.$removeButton.prop('disabled', this.$label.val() === this.$container.data('placeholder'));

        this.$removeButton.click(() => {
            this.selectProduct('', this.$container.data('placeholder'));
            return false;
        });
    }

    onSelectProduct(productId, productName) {
        if (this.onSelectProductCallback !== undefined) {
            this.onSelectProductCallback(productId, productName);
        } else {
            this.selectProduct(productId, productName);
        }
    }

    makePicker(event) {
        const url = this.$pickerButton.data('product-picker-url').replace('__instance_id__', this.instanceId);

        const iframeContent = `<iframe src="${url}" style="width: 100%; height: 800px; border: none;"></iframe>`;

        this.modal = new ModalWindow({
            content: iframeContent,
            title: Translator.trans('Assign product'),
            size: 'xl',
            buttons: [{ text: Translator.trans('Finish assigning') }],
        });

        event.preventDefault();
    }

    selectProduct(productId, productName) {
        this.$input.val(productId);
        this.$label.val(productName);
        this.$removeButton.prop('disabled', productId === '');
    }

    static onClickSelectProduct(instanceId, productId, productName) {
        const pickerInstance = window.parent.ProductPickerInstances[instanceId];

        if (!pickerInstance) {
            console.error(`ProductPicker instance ${instanceId} not found.`);

            return;
        }

        pickerInstance.onSelectProduct(productId, productName);

        if (pickerInstance.modal?.element && typeof pickerInstance.modal.element.modal === 'function') {
            pickerInstance.modal.element.modal('hide');
        }
    }

    static init($container) {
        $container.filterAllNodes('.js-product-picker-create-picker-button').each(function () {
            // eslint-disable-next-line no-new
            new ProductPicker($(this));
        });

        $('.js-product-picker-select').click(event => {
            const $btnElement = $(event.currentTarget);
            ProductPicker.onClickSelectProduct(
                $btnElement.data('instance-id'),
                $btnElement.data('product-id'),
                $btnElement.data('product-name'),
            );
        });
    }
}

new Register().registerCallback(ProductPicker.init, 'ProductPicker.init');
