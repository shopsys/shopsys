import Sortable from 'sortablejs';
import Ajax from '../../common/utils/Ajax';
import Window from '../utils/Window';
import Register from '../../common/utils/Register';
import Translator from 'bazinga-translator';

export default class GridMultipleDragAndDrop {

    constructor ($content) {
        this.toggleRowHolders($content);

        const _this = this;
        $content.find('.js-multiple-grids-save-all-button').click((event) => this.saveOrdering($content, event));
        $content.find('.js-inline-edit-rows').each(function () {
            Sortable.create(this, {
                group: 'multiple-grids',
                handle: '.js-move-handle',
                draggable: '.js-grid-row',
                animation: 150,
                onChange: () => _this.onUpdate($content),
                onEnd: () => _this.onUpdate($content)
            });
        });
    }

    saveOrdering ($content, event) {
        const $saveButton = $(event.target);
        const $grids = $saveButton.closest('.js-multiple-grids-rows-unified').find('.js-grid');
        const data = {
            rowIdsByGridId: this.getPositionsIndexedByGridId($grids)
        };

        Ajax.ajax({
            loaderElement: $content.find('.js-multiple-grids-save-all-button'),
            url: $saveButton.data('drag-and-drop-url-save-ordering'),
            type: 'POST',
            data,
            dataType: 'json',
            success: function () {
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

        $saveButton.addClass('btn--disabled');
    }

    getPositionsIndexedByGridId ($grids) {
        const rowIdsIndexedByGridId = {};
        $.each($grids, function (index, grid) {
            const $grid = $(grid);
            const gridId = $grid.data('grid-id');
            rowIdsIndexedByGridId[gridId] = {};
            const rows = $grid.find('.js-grid-row');

            $.each(rows, function (rowIndex, row) {
                rowIdsIndexedByGridId[gridId][rowIndex] = $(row).data('drag-and-drop-grid-row-id');
            });
        });

        return rowIdsIndexedByGridId;
    }

    toggleRowHolders ($content) {
        $content.find('.js-multiple-grids-rows-unified .js-grid').each(function () {
            const gridRowsCount = $(this).find('.js-grid-row:not(.ui-sortable-helper):not(.js-grid-row-holder), .in-drop-place').length;
            const $rowHolder = $(this).find('.js-grid-row-holder');
            $rowHolder.toggle(gridRowsCount === 0);
        });
    }

    onUpdate ($content) {
        $content.find('.js-multiple-grids-save-all-button').removeClass('btn--disabled');
        this.toggleRowHolders($content);
    }

    static init ($content) {
        // eslint-disable-next-line no-new
        new GridMultipleDragAndDrop($content);
    }
}

(new Register()).registerCallback(GridMultipleDragAndDrop.init, 'GridMultipleDragAndDrop.init');
