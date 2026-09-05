import $ from 'jquery';
import Register from '../../common/utils/Register';
import FilePicker from './FilePicker';
import FormChangeInfo from './FormChangeInfo';
import MultiplePicker from './MultiplePicker';

export default class FilesPicker extends MultiplePicker {
    constructor($picker) {
        super($picker);

        this.$addButton = $(`[data-picker-target="${$picker.attr('id')}"]`);
        this.$addButton.click(() => this.openPickerWindow());
    }

    addItem($selectedElement) {
        const nextIndex = this.$itemsContainer.find('.js-picker-item').length;
        const itemHtml = this.$picker.data('picker-prototype').replace(/__name__/g, nextIndex);
        const $item = $($.parseHTML(itemHtml));

        FilePicker.populateItemFromSelection($item, $selectedElement);

        this.$itemsContainer.append($item);
        this.initItem($item);
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    static init($container) {
        $container.filterAllNodes('.js-files-picker').each(function () {
            // eslint-disable-next-line no-new
            new FilesPicker($(this));
        });
    }
}

new Register().registerCallback(FilesPicker.init, 'FilesPicker.init');
