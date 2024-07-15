import 'magnific-popup';
import Register from '../../common/utils/Register';

window.PickerInstances = {};

export default class Picker {
    constructor ($pickerButton, onSelectCallback) {
        this.instanceId = Object.keys(window.PickerInstances).length;
        window.PickerInstances[this.instanceId] = this;

        const _this = this;
        this.$pickerButton = $pickerButton;
        this.onSelectCallback = onSelectCallback;
        this.$container = this.$pickerButton.closest('.js-picker-container');
        this.$input = this.$container.find('.js-picker-input');
        this.$label = this.$container.find('.js-picker-label');
        this.$removeButton = this.$container.find('.js-picker-remove-button');

        this.$pickerButton.click((event) => _this.makePicker(event));
        this.$removeButton.toggle(this.$label.val() !== this.$container.data('placeholder'));

        this.$removeButton.click(() => {
            _this.select('', _this.$container.data('placeholder'));
            return false;
        });
    }

    onSelect (productId, productName) {
        if (this.onSelectCallback !== undefined) {
            this.onSelectCallback(productId, productName);
        } else {
            this.select(productId, productName);
        }
    }

    makePicker (event) {
        const _this = this;
        $.magnificPopup.open({
            items: { src: _this.$pickerButton.data('picker-url').replace('__instance_id__', _this.instanceId) },
            type: 'iframe',
            closeOnBgClick: true
        });

        event.preventDefault();
    }

    select (productId, productName) {
        this.$input.val(productId);
        this.$label.val(productName);
        this.$removeButton.toggle(productId !== '');
    }

    static onClickSelect (instanceId, productId, productName) {
        window.parent.PickerInstances[instanceId].onSelect(productId, productName);
        window.parent.$.magnificPopup.instance.close();
    }

    static init ($container) {
        $container.filterAllNodes('.js-picker-create-picker-button').each(function () {
            // eslint-disable-next-line no-new
            new Picker($(this));
        });

        $('.js-picker-select').click((event) => {
            const $btnElement = $(event.currentTarget);
            Picker.onClickSelect(
                $btnElement.data('instance-id'),
                $btnElement.data('picker-id'),
                $btnElement.data('picker-name')
            );
        });
    }
}

(new Register()).registerCallback(Picker.init, 'Picker.init');
