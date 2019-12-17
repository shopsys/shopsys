import $ from 'jquery';
import 'magnific-popup';
import Register from '../common/register';

window.ProductPickerInstances = {};

export default class ProductPicker {

    constructor ($pickerButton, onSelectProductCallback) {
        this.instanceId = Object.keys(window.ProductPickerInstances).length;
        window.ProductPickerInstances[this.instanceId] = this;

        const _this = this;
        this.$pickerButton = $pickerButton;
        this.onSelectProductCallback = onSelectProductCallback;
        this.$container = this.$pickerButton.closest('.js-product-picker-container');
        this.$input = this.$container.find('.js-product-picker-input');
        this.$label = this.$container.find('.js-product-picker-label');
        this.$removeButton = this.$container.find('.js-product-picker-remove-button');

        this.$pickerButton.click((event) => _this.makePicker(event));
        this.$removeButton.toggle(this.$label.val() !== this.$container.data('placeholder'));

        this.$removeButton.click(function () {
            _this.selectProduct('', this.$container.data('placeholder'));
            return false;
        });
    }

    onSelectProduct (productId, productName) {
        if (this.onSelectProductCallback !== undefined) {
            this.onSelectProductCallback(productId, productName);
        } else {
            this.selectProduct(productId, productName);
        }
    }

    makePicker (event) {
        const _this = this;
        $.magnificPopup.open({
            items: { src: _this.$pickerButton.data('product-picker-url').replace('__instance_id__', _this.instanceId) },
            type: 'iframe',
            closeOnBgClick: true
        });

        event.preventDefault();
    };

    selectProduct (productId, productName) {
        this.$input.val(productId);
        this.$label.val(productName);
        this.$removeButton.toggle(productId !== '');
    }

    static onClickSelectProduct (instanceId, productId, productName) {
        window.parent.ProductPickerInstances[instanceId].onSelectProduct(productId, productName);
        window.parent.$.magnificPopup.instance.close();
    }

    static init ($container) {
        $container.filterAllNodes('.js-product-picker-create-picker-button').each(function () {
            // eslint-disable-next-line no-new
            new ProductPicker($(this));
        });

        $('.js-product-picker-select').click((event) => {
            const $btnElement = $(event.currentTarget);
            ProductPicker.onClickSelectProduct(
                $btnElement.data('instance-id'),
                $btnElement.data('product-id'),
                $btnElement.data('product-name')
            );
        });
    }
}

(new Register()).registerCallback(ProductPicker.init);
