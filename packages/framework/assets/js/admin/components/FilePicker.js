import $ from 'jquery';
import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';
import MultiplePicker from './MultiplePicker';

export default class FilePicker extends MultiplePicker {
    constructor($picker) {
        super($picker);

        this.$addButton = $(`[data-picker-target="${$picker.attr('id')}"]`);
        this.$addButton.click(() => this.openPickerWindow());
    }

    getPickerWindowTitle() {
        return Translator.trans('Select file');
    }

    getPickerWindowButtons() {
        return [{ text: Translator.trans('Cancel') }];
    }

    addItem($selectedElement) {
        const $existingItem = this.$itemsContainer.find('.js-picker-item');

        if ($existingItem.length > 0) {
            this.removeItem($existingItem);
        }

        const itemHtml = this.$picker.data('picker-prototype').replace(/__name__/g, 0);
        const $item = $($.parseHTML(itemHtml));

        FilePicker.populateItemFromSelection($item, $selectedElement);

        this.$itemsContainer.append($item);
        this.initItem($item);
        FormChangeInfo.showInfo();

        if (this.modal?.element && typeof this.modal.element.modal === 'function') {
            this.modal.element.modal('hide');
        }
    }

    static populateItemFromSelection($item, $selectedElement) {
        $item.find('.js-picker-item-input').val($selectedElement.data('picker-id'));
        $item.find('.js-picker-item-thumbnail').html($selectedElement.data('picker-thumbnail'));
        $item.find('.js-picker-item-filename').val($selectedElement.data('picker-filename'));
        $item.find('.js-picker-item-name').text($selectedElement.data('picker-name'));

        const names = $selectedElement.data('picker-names');
        const namesInputs = $item.find('.js-picker-item-names');

        for (const locale in names) {
            namesInputs.find(`input[data-locale="${locale}"]`).val(names[locale]);
        }
    }

    static onClickSelectFile(instanceId, $btnElement) {
        const pickerInstance = window.parent.PickerInstances[instanceId];

        if (!pickerInstance) {
            console.error(`FilePicker instance ${instanceId} not found.`);

            return;
        }

        pickerInstance.addItem($btnElement);
    }

    static init($container) {
        $container.filterAllNodes('.js-file-picker').each(function () {
            // eslint-disable-next-line no-new
            new FilePicker($(this));
        });

        $('.js-file-picker-select').click(event => {
            const $btnElement = $(event.currentTarget);
            FilePicker.onClickSelectFile($btnElement.data('picker-instance-id'), $btnElement);
        });
    }
}

new Register().registerCallback(FilePicker.init, 'FilePicker.init');
