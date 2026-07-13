import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

window.SinglePickerInstances = {};

export default class SinglePicker {
    constructor($picker, onSelectCallback) {
        this.instanceId = Object.keys(window.SinglePickerInstances).length;
        window.SinglePickerInstances[this.instanceId] = this;
        this.$picker = $picker;
        this.onSelectCallback = onSelectCallback;

        if (onSelectCallback === undefined) {
            this.$input = $picker.find('[data-js-single-picker-input]');
            this.$label = $picker.find('[data-js-single-picker-label]');
            this.$addButton = $picker.find('[data-js-single-picker-button-add]');
            this.$removeButton = $picker.find('[data-js-single-picker-button-remove]');

            this.$removeButton.prop('disabled', this.$input.val() === '');
            this.$removeButton.click(() => {
                this.select('', $picker.data('placeholder'));

                return false;
            });

            this.$label.click(event => this.openPickerWindow(event));
        } else {
            this.$addButton = $picker;
        }

        this.$addButton.click(event => this.openPickerWindow(event));
    }

    openPickerWindow(event) {
        const url = this.$picker.data('picker-url').replace('__js_instance_id__', this.instanceId);

        const iframeContent = `<iframe src="${url}" style="width: 100%; height: 800px; border: none;"></iframe>`;

        this.modal = new ModalWindow({
            content: iframeContent,
            title: this.$picker.data('picker-title') || Translator.trans('Select'),
            size: 'xl',
            buttons: [{ text: Translator.trans('Close') }],
        });

        event.preventDefault();
    }

    select(id, name) {
        if (this.onSelectCallback !== undefined) {
            this.onSelectCallback(id, name);
        } else {
            this.$input.val(id);
            this.$label.val(name);
            this.$removeButton.prop('disabled', id === '');
            FormChangeInfo.showInfo();
        }
    }

    static onClickSelect(instanceId, id, name) {
        const pickerInstance = window.parent.SinglePickerInstances[instanceId];

        if (!pickerInstance) {
            console.error(`SinglePicker instance ${instanceId} not found.`);

            return;
        }

        pickerInstance.select(id, name);

        if (pickerInstance.modal?.element && typeof pickerInstance.modal.element.modal === 'function') {
            pickerInstance.modal.element.modal('hide');
        }
    }

    static init($container) {
        $container.filterAllNodes('[data-js-single-picker]').each(function () {
            // eslint-disable-next-line no-new
            new SinglePicker($(this));
        });

        $('[data-js-single-picker-window-select]').click(event => {
            const $btnElement = $(event.currentTarget);
            SinglePicker.onClickSelect(
                $btnElement.data('picker-instance-id'),
                $btnElement.data('picker-id'),
                $btnElement.data('picker-name'),
            );
        });
    }
}

new Register().registerCallback(SinglePicker.init, 'SinglePicker.init');
