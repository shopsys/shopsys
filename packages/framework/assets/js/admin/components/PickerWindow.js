import '../../common/bootstrap/tooltip';
import Register from '../../common/utils/Register';
import Translator from 'bazinga-translator';

export default class PickerWindow {
    constructor ($addButton) {
        $addButton.on('click.addItem', (event) => this.onClickAddButton(event));
    }

    markAddButtonAsAdded ($addButton) {
        const originalLabelText = $addButton.find('.js-picker-label').text();
        const originalIconText = $addButton.find('.js-picker-icon').text();
        $addButton
            .addClass('cursor-auto btn--success').removeClass('btn--plus btn--light')
            .find('.js-picker-label').text(Translator.trans('Added')).end()
            .find('.js-picker-icon').addClass('svg svg-checked').empty().end()
            .on('click.removeItem', () => {
                this.onClickOnAddedButton($addButton, originalLabelText, originalIconText);
            })
            .click(function () {
                return false;
            });
    }

    markAddButtonAsDeny ($addButton) {
        $addButton
            .addClass('cursor-help')
            .tooltip({
                title: Translator.trans('Not possible to assign to itself'),
                placement: 'left'
            })
            .find('.js-picker-label').text(Translator.trans('Unable to add'))
            .find('.js-picker-icon').removeClass('svg-circle-plus in-icon in-icon--add').addClass('svg-circle-remove in-icon in-icon--denied').end()
            .click(() => false);
    }

    onClickAddButton (event) {
        const picker = window.parent.PickerInstances[$(event.currentTarget).data('picker-instance-id')];
        this.markAddButtonAsAdded($(event.currentTarget));
        $(event.currentTarget).off('click.addItem');
        picker.addItem(
            $(event.currentTarget).data('picker-id'),
            $(event.currentTarget).data('picker-name')
        );

        return false;
    }

    onClickOnAddedButton ($addButton, originalLabelText, originalIconText) {
        const picker = window.parent.PickerInstances[$addButton.data('picker-instance-id')];
        this.unmarkAddButtonAsAdded($addButton, originalLabelText, originalIconText);
        $addButton.off('click.removeItem');
        picker.removeItemById($addButton.data('picker-id'));

        return false;
    }

    unmarkAddButtonAsAdded ($addButton, originalLabelText, originalIconText) {
        $addButton
            .addClass('btn--plus btn--light').removeClass('cursor-auto btn--success')
            .find('.js-picker-label').text(originalLabelText).end()
            .find('.js-picker-icon').removeClass('svg svg-checked').text(originalIconText).end()
            .on('click.addItem', (event) => this.onClickAddButton(event))
            .click(() => false);
    }

    static init ($container) {
        $container.filterAllNodes('.js-picker-window-add-item').each(function () {
            // eslint-disable-next-line no-new
            new PickerWindow($(this));
        });
    }
}

(new Register()).registerCallback(PickerWindow.init, 'PickerWindow.init');
