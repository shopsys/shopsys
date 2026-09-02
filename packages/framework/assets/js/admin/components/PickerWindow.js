import $ from 'jquery';
import Translator from 'bazinga-translator';
import Check from 'icons/tabler/check.svg?raw';
import Denied from 'icons/tabler/circle-x-filled.svg?raw';
import Register from '../../common/utils/Register';

export default class PickerWindow {
    constructor($addButton) {
        const picker = window.parent.PickerInstances[$addButton.data('picker-instance-id')];
        const id = $addButton.data('picker-id');

        if (picker.hasItem(id)) {
            this.markAddButtonAsAdded($addButton);
        }

        $addButton.on('click.addItem', event => this.onClickAddButton(event));
    }

    markAddButtonAsAdded($addButton) {
        const originalLabelText = $addButton.find('.js-picker-label').text();
        const originalIconHtml = $addButton.find('.js-picker-icon').html();
        $addButton
            .addClass('btn-success')
            .find('.js-picker-label')
            .text(Translator.trans('Added'))
            .end()
            .find('.js-picker-icon')
            .html(Check)
            .end()
            .on('click.removeItem', () => {
                this.onClickOnAddedButton($addButton, originalLabelText, originalIconHtml);
            })
            .click(() => false);
    }

    markAddButtonAsDeny($addButton) {
        $addButton
            .addClass('cursor-help')
            .tooltip({
                title: Translator.trans('Not possible to assign to itself'),
                placement: 'left',
            })
            .find('.js-picker-label')
            .text(Translator.trans('Unable to add'))
            .end()
            .find('.js-picker-icon')
            .html(Denied)
            .end()
            .click(() => false);
    }

    onClickAddButton(event) {
        const picker = window.parent.PickerInstances[$(event.currentTarget).data('picker-instance-id')];
        this.markAddButtonAsAdded($(event.currentTarget));
        $(event.currentTarget).off('click.addItem');
        picker.addItem($(event.currentTarget));

        return false;
    }

    onClickOnAddedButton($addButton, originalLabelText, originalIconHtml) {
        const picker = window.parent.PickerInstances[$addButton.data('picker-instance-id')];
        this.unmarkAddButtonAsAdded($addButton, originalLabelText, originalIconHtml);
        $addButton.off('click.removeItem');
        picker.removeItemById($addButton.data('picker-id'));

        return false;
    }

    unmarkAddButtonAsAdded($addButton, originalLabelText, originalIconHtml) {
        $addButton
            .removeClass('btn-success')
            .find('.js-picker-label')
            .text(originalLabelText)
            .end()
            .find('.js-picker-icon')
            .html(originalIconHtml)
            .end()
            .on('click.addItem', event => this.onClickAddButton(event))
            .click(() => false);
    }

    static init($container) {
        $container.filterAllNodes('.js-picker-window-add-item').each(function () {
            // eslint-disable-next-line no-new
            new PickerWindow($(this));
        });
    }
}

new Register().registerCallback(PickerWindow.init, 'PickerWindow.init');
