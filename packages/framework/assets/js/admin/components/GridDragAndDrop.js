import $ from 'jquery';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Sortable from 'sortablejs';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

export default class GridDragAndDrop {
    constructor($content) {
        const _this = this;

        $content.find('.js-drag-and-drop-grid-rows').each(function (_index) {
            Sortable.create(this, {
                handle: '.js-move-handle',
                draggable: '.js-grid-row',
                animation: 150,
                onChange: event => _this.onUpdate(event),
            });
        });

        $content.find('.js-grid').each(function () {
            const $grid = $(this);
            _this.initGrid($grid);
        });

        this.unifyMultipleGrids($content);
    }

    initGrid($grid) {
        $grid.find('.js-drag-and-drop-grid-submit').click(() => {
            if (!$grid.data('positionsChanged')) {
                return false;
            }

            this.saveOrdering($grid);
        });

        $grid.data('positionsChanged', false);
        this.highlightChanges($grid, false);
    }

    onUpdate(event, _ui) {
        const $grid = $(event.target).closest('.js-grid');

        $grid.data('positionsChanged', true);
        this.highlightChanges($grid, true);
        $grid.trigger('update');
    }

    highlightChanges($grid, highlight) {
        if (highlight) {
            $grid.find('.js-drag-and-drop-grid-submit').prop('disabled', false);
            FormChangeInfo.showInfo();
        } else {
            $grid.find('.js-drag-and-drop-grid-submit').prop('disabled', true);
            FormChangeInfo.removeInfo();
        }
    }

    unifyMultipleGrids($content) {
        const $gridSaveButtons = $content.find('.js-drag-and-drop-grid-submit');
        const $gridsOnPage = $content.find('.js-grid[data-drag-and-drop-ordering-entity-class]');
        const $saveAllButton = $content.find('.js-drag-and-drop-grid-submit-all');

        if ($saveAllButton.length === 1) {
            $gridSaveButtons.hide();

            $gridsOnPage.on('update', () => {
                FormChangeInfo.showInfo();
                $saveAllButton.prop('disabled', false);
            });

            $gridsOnPage.on('save', () => {
                FormChangeInfo.removeInfo();
                $saveAllButton.prop('disabled', true);
            });

            $saveAllButton.click(() => {
                $gridSaveButtons.click();
            });
        }
    }

    saveOrdering($grid, _rowIds) {
        const data = {
            entityClass: $grid.data('drag-and-drop-ordering-entity-class'),
            rowIds: this.getPositions($grid),
        };
        Ajax.ajax({
            loaderElement: $grid.find('.js-drag-and-drop-grid-submit, js-drag-and-drop-grid-submit-all'),
            url: $grid.data('drag-and-drop-url-save-ordering'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: () => {
                $grid.data('positionsChanged', false);
                this.highlightChanges($grid, false);

                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Order saved'),
                });
            },
        });
        $grid.trigger('save');
    }

    getPositions($grid) {
        const rows = $grid.find('.js-grid-row');

        const rowIds = [];
        $.each(rows, (_index, row) => {
            rowIds.push($(row).data('drag-and-drop-grid-row-id'));
        });

        return rowIds;
    }

    static init($content) {
        // eslint-disable-next-line no-new
        new GridDragAndDrop($content);
    }
}

new Register().registerCallback(GridDragAndDrop.init, 'GridDragAndDrop.init');
