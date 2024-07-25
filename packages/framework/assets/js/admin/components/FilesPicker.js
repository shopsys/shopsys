import 'magnific-popup';
import 'jquery-ui/sortable';
import 'jquery-ui/ui/widgets/mouse';
import 'jquery-ui-touch-punch';
import FormChangeInfo from './FormChangeInfo';
import Register from '../../common/utils/Register';
import MultiplePicker from './MultiplePicker';

export default class FilesPicker extends MultiplePicker {

    constructor ($picker) {
        super($picker);

        this.$addButton = $('[data-picker-target="' + $picker.attr('id') + '"]');
        this.$addButton.click(() => this.openPickerWindow());
    }
    addItem ($selectedElement) {
        const nextIndex = this.$itemsContainer.find('.js-picker-item').length;
        const itemHtml = this.$picker.data('picker-prototype').replace(/__name__/g, nextIndex);
        const $item = $($.parseHTML(itemHtml));

        $item.find('.js-picker-item-input').val($selectedElement.data('picker-id'));
        $item.find('.js-picker-item-thumbnail').html($selectedElement.data('picker-thumbnail'));
        $item.find('.js-picker-item-filename').val($selectedElement.data('picker-filename'));
        $item.find('.js-picker-item-name').text($selectedElement.data('picker-name'));

        const names = $selectedElement.data('picker-names');
        const namesInputs = $item.find('.js-picker-item-names');
        for (let locale in names) {
            namesInputs.find('input[data-locale="' + locale + '"]').val(names[locale]);
        }

        this.$itemsContainer.append($item);
        this.initItem($item);
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    static init ($container) {
        $container.filterAllNodes('.js-files-picker').each(function () {
            // eslint-disable-next-line no-new
            new FilesPicker($(this));
        });
    }
}

(new Register().registerCallback(FilesPicker.init, 'FilesPicker.init'));
