import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

window.FilePickerInstances = {};

export default class FilePicker {
    constructor($picker) {
        this.instanceId = Object.keys(window.FilePickerInstances).length;
        window.FilePickerInstances[this.instanceId] = this;

        this.$picker = $picker;
        this.$addButton = $(`[data-picker-target="${$picker.attr('id')}"]`);
        this.$itemsContainer = $picker.find('.js-picker-items');

        this.$addButton.click(event => this.openPicker(event));
        this.initExistingItem();
    }

    initExistingItem() {
        const $existingItem = this.$itemsContainer.find('.js-picker-item');

        if ($existingItem.length > 0) {
            $existingItem.find('.js-picker-item-button-delete').click(() => {
                this.removeItem($existingItem);
            });
        }
    }

    openPicker(event) {
        event.preventDefault();

        const url = this.$picker.data('picker-url').replace('__js_instance_id__', this.instanceId);
        const iframeContent = `<iframe src="${url}" style="width: 100%; height: 800px; border: none;"></iframe>`;

        this.modal = new ModalWindow({
            content: iframeContent,
            title: Translator.trans('Select file'),
            size: 'xl',
            buttons: [{ text: Translator.trans('Cancel') }],
        });

        return false;
    }

    addItem($selectedElement) {
        this.$itemsContainer.find('.js-picker-item').remove();

        const itemHtml = this.$picker.data('picker-prototype').replace(/__name__/g, 0);
        const $item = $($.parseHTML(itemHtml));

        FilePicker.populateItemFromSelection($item, $selectedElement);

        this.$itemsContainer.append($item);

        $item.find('.js-picker-item-button-delete').click(() => {
            this.removeItem($item);
        });

        FormChangeInfo.showInfo();

        if (this.modal?.element && typeof this.modal.element.modal === 'function') {
            this.modal.element.modal('hide');
        }
    }

    removeItem($item) {
        $item.remove();
        FormChangeInfo.showInfo();
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
        const pickerInstance = window.parent.FilePickerInstances[instanceId];

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
