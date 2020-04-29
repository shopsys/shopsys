import 'jquery-ui/sortable';
import 'jquery-ui/ui/widgets/mouse';
import 'jquery-ui-touch-punch';
import formChangeInfo from './FormChangeInfo';
import Ajax from '../../common/utils/Ajax';
import Window from '../utils/Window';
import Register from '../../common/utils/Register';
import Translator from 'bazinga-translator';

export default class GridDragAndDrop {

    constructor ($content) {
        const _this = this;
        $content.find('.js-drag-and-drop-grid-rows').sortable({
            cursor: 'move',
            handle: '.cursor-move',
            items: '.js-grid-row',
            placeholder: 'in-drop-place',
            revert: 200,
            update: (event) => _this.onUpdate(event)
        });

        $content.find('.js-grid').each(function () {
            const $grid = $(this);
            _this.initGrid($grid);
        });

        this.unifyMultipleGrids($content);
    }

    initGrid ($grid) {
        const _this = this;
        $grid.find('.js-drag-and-drop-grid-submit').click(() => {
            if (!$grid.data('positionsChanged')) {
                return false;
            }

            _this.saveOrdering($grid);
        });

        $grid.data('positionsChanged', false);
        this.highlightChanges($grid, false);
    }

    onUpdate (event, ui) {
        const $grid = $(event.target).closest('.js-grid');

        $grid.data('positionsChanged', true);
        this.highlightChanges($grid, true);
        $grid.trigger('update');
    }

    highlightChanges ($grid, highlight) {
        if (highlight) {
            $grid.find('.js-drag-and-drop-grid-submit').removeClass('btn--disabled');
        } else {
            $grid.find('.js-drag-and-drop-grid-submit').addClass('btn--disabled');
        }
    }

    unifyMultipleGrids ($content) {
        const $gridSaveButtons = $content.find('.js-drag-and-drop-grid-submit');
        const $gridsOnPage = $content.find('.js-grid[data-drag-and-drop-ordering-entity-class]');
        const $saveAllButton = $content.find('.js-drag-and-drop-grid-submit-all');

        if ($saveAllButton.length === 1) {
            $gridSaveButtons.hide();

            $gridsOnPage.on('update', function () {
                formChangeInfo.showInfo();
                $saveAllButton.removeClass('btn--disabled');
            });

            $gridsOnPage.on('save', function () {
                formChangeInfo.removeInfo();
                $saveAllButton.addClass('btn--disabled');
            });

            $saveAllButton.click(function () {
                $gridSaveButtons.click();
            });
        }
    }

    saveOrdering ($grid, rowIds) {
        const data = {
            entityClass: $grid.data('drag-and-drop-ordering-entity-class'),
            rowIds: this.getPositions($grid)
        };

        const _this = this;
        Ajax.ajax({
            loaderElement: $grid.find('.js-drag-and-drop-grid-submit, js-drag-and-drop-grid-submit-all'),
            url: $grid.data('drag-and-drop-url-save-ordering'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function () {
                $grid.data('positionsChanged', false);
                _this.highlightChanges($grid, false);

                // eslint-disable-next-line no-new
                new Window({
                    content: Translator.trans('Order saved')
                });
            },
            error: function () {
                // eslint-disable-next-line no-new
                new Window({
                    content: Translator.trans('Order saving failed')
                });
            }
        });
        $grid.trigger('save');
    }

    getPositions ($grid) {
        const rows = $grid.find('.js-grid-row');

        const rowIds = [];
        $.each(rows, function (index, row) {
            rowIds.push($(row).data('drag-and-drop-grid-row-id'));
        });

        return rowIds;
    }

    static init ($content) {
        // eslint-disable-next-line no-new
        new GridDragAndDrop($content);
    }
}

(new Register()).registerCallback(GridDragAndDrop.init, 'GridDragAndDrop.init');
