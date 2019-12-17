import { KeyCodes } from '../../common/components/keyCodes';
import Ajax from '../../common/ajax';
import Register from '../../common/register';
import Window from '../window';

export default class GridInlineEdit {

    constructor (item) {
        const $grid = $(item);
        const _this = this;
        $grid
            .off('click', '.js-inline-edit-edit')
            .on('click', '.js-inline-edit-edit', function () {
                const $row = $(this).closest('.js-grid-row');
                if (_this.isRowEnabled($row)) {
                    _this.disableRow($row);
                    _this.startEditRow($row, $grid);
                }
                return false;
            });

        $grid
            .off('click', '.js-inline-edit-add')
            .on('click', '.js-inline-edit-add', function () {
                $grid.find('.js-inline-edit-no-data').remove();
                $grid.find('.js-inline-edit-data-container').removeClass('hidden');
                _this.addNewRow($grid);
                return false;
            });

        $grid
            .off('click', '.js-inline-edit-cancel')
            .on('click', '.js-inline-edit-cancel', function () {
                const $formRow = $(this).closest('.js-grid-editing-row');
                // eslint-disable-next-line no-new
                new Window({
                    // content: Shopsys.translator.trans('Do you really want to discard all changes?'),
                    content: 'Do you really want to discard all changes?',
                    buttonCancel: true,
                    buttonContinue: true,
                    // textContinue: Shopsys.translator.trans('Yes'),
                    textContinue: 'Yes',
                    eventContinue: function () {
                        _this.cancelEdit($formRow);
                    }
                });
                return false;
            });

        $grid
            .off('click', '.js-inline-edit-save')
            .on('click', '.js-inline-edit-save', function () {
                _this.saveRow($(this).closest('.js-grid-editing-row'), $grid);
                $grid.find('.js-drag-and-drop-grid-rows').sortable('enable');
                return false;
            });

        $grid
            .off('keyup', '.js-grid-editing-row input')
            .on('keyup', '.js-grid-editing-row input', function (event) {
                if (event.keyCode == KeyCodes.ENTER) {
                    _this.saveRow($(this).closest('.js-grid-editing-row'), $grid);
                }
                return false;
            });
    }

    saveRow ($formRow, $grid) {
        const $buttons = $formRow.find('.js-inline-edit-buttons').hide();
        const $saving = $formRow.find('.js-inline-edit-saving').show();
        const $virtualForm = $('<form>')
            .append($formRow.clone())
            .append($('<input type="hidden" name="serviceName" />')
                .val($grid.data('inline-edit-service-name')));

        const $originalRow = $formRow.data('$originalRow');
        if ($originalRow) {
            $virtualForm.append($('<input type="hidden" name="rowId" />').val($originalRow.data('inline-edit-row-id')));
            $originalRow.data('inline-edit-row-id');
        }

        Ajax.ajax({
            url: $grid.data('inline-edit-url-save-form'),
            type: 'POST',
            data: $virtualForm.serialize(),
            dataType: 'json',
            success: function (saveResult) {
                if (saveResult.success) {
                    const $newRow = $(saveResult.rowHtml);
                    $formRow.replaceWith($newRow).remove();
                    (new Register()).registerNewContent($newRow);
                } else {
                    $buttons.show();
                    $saving.hide();
                    // eslint-disable-next-line no-new
                    new Window({
                        // content: Shopsys.translator.trans('Please check following information:') + '<br/><br/>• ' + saveResult.errors.join('<br/>• ')
                        content: 'Please check following information:<br/><br/> ' + saveResult.errors.join('<br/>• ')
                    });
                }
            },
            error: function () {
                // eslint-disable-next-line no-new
                new Window({
                    // content: Shopsys.translator.trans('Error occurred, try again please.')
                    content: 'Error occurred, try again please.'
                });
                $buttons.show();
                $saving.hide();
            }
        });
    }

    startEditRow ($row, $grid) {
        Ajax.ajax({
            url: $grid.data('inline-edit-url-get-form'),
            type: 'POST',
            data: {
                serviceName: $grid.data('inline-edit-service-name'),
                rowId: $row.data('inline-edit-row-id')
            },
            dataType: 'json',
            success: function (formRowData) {
                const $formRow = $($.parseHTML(formRowData));
                $formRow.addClass('js-grid-editing-row');
                $formRow.find('.js-inline-edit-saving').hide();
                $row.replaceWith($formRow);
                (new Register()).registerNewContent($formRow);
                $formRow.data('$originalRow', $row);
            }
        });
    }

    addNewRow ($grid) {
        Ajax.ajax({
            url: $grid.data('inline-edit-url-get-form'),
            type: 'POST',
            data: {
                serviceName: $grid.data('inline-edit-service-name')
            },
            dataType: 'json',
            success: function (formRowData) {
                const $formRow = $($.parseHTML(formRowData));
                $formRow.addClass('js-grid-editing-row');
                $formRow.find('.js-inline-edit-saving').hide();
                (new Register()).registerNewContent($formRow);
                $grid.find('.js-inline-edit-rows').prepend($formRow);
                $formRow.find('input[type=text]:first').focus();
                $grid.find('.js-drag-and-drop-grid-rows').sortable('disable');
            }
        });
    }

    cancelEdit ($formRow) {
        const $originalRow = $formRow.data('$originalRow');
        if ($originalRow) {
            $formRow.replaceWith($originalRow).remove();
            (new Register()).registerNewContent($originalRow);
            this.enableRow($originalRow);
        }
        $formRow.remove();
    }

    disableRow ($row) {
        return $row.addClass('js-inactive');
    }

    enableRow ($row) {
        return $row.removeClass('js-inactive');
    }

    isRowEnabled ($row) {
        return !$row.hasClass('js-inactive');
    }

    static init () {
        $('.js-grid[data-inline-edit-service-name]').each((idx, grid) => {
            // eslint-disable-next-line no-new
            new GridInlineEdit(grid);
        });
    }

}

(new Register()).registerCallback(GridInlineEdit.init);
