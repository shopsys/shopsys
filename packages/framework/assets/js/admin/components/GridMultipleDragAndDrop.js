import $ from 'jquery';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Sortable from 'sortablejs';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

export default class GridMultipleDragAndDrop {
    constructor($content) {
        // Only initialize if multiple grids mode is active
        const $multipleGridsContainer = $content.find('.js-multiple-grids-rows-unified');

        if ($multipleGridsContainer.length === 0) {
            return;
        }

        this.toggleRowHolders($content);

        const _this = this;
        $content.find('.js-multiple-grids-save-all-button').click(event => this.saveOrdering($content, event));
        $content.find('.js-inline-edit-rows').each(function () {
            Sortable.create(this, {
                group: 'multiple-grids',
                handle: '.js-move-handle',
                draggable: '.js-grid-row',
                animation: 150,
                onChange: () => _this.onUpdate($content),
                onEnd: () => _this.onUpdate($content),
            });
        });
    }

    saveOrdering($content, event) {
        const $saveButton = $(event.target);
        const $grids = $saveButton.closest('.js-multiple-grids-rows-unified').find('.js-grid');
        const data = {
            rowIdsByGridId: this.getPositionsIndexedByGridId($grids),
        };

        Ajax.ajax({
            loaderElement: $content.find('.js-multiple-grids-save-all-button'),
            url: $saveButton.data('drag-and-drop-url-save-ordering'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: () => {
                FormChangeInfo.removeInfo();

                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Order saved'),
                });
            },
            error: () => {
                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Order saving failed'),
                });
            },
        });

        $saveButton.prop('disabled', true);
    }

    getPositionsIndexedByGridId($grids) {
        const rowIdsIndexedByGridId = {};
        $.each($grids, (_index, grid) => {
            const $grid = $(grid);
            const gridId = $grid.data('grid-id');
            rowIdsIndexedByGridId[gridId] = {};
            const rows = $grid.find('.js-grid-row');

            $.each(rows, (rowIndex, row) => {
                rowIdsIndexedByGridId[gridId][rowIndex] = $(row).data('drag-and-drop-grid-row-id');
            });
        });

        return rowIdsIndexedByGridId;
    }

    toggleRowHolders($content) {
        $content.find('.js-multiple-grids-rows-unified .js-grid').each(function () {
            const gridRowsCount = $(this).find(
                '.js-grid-row:not(.ui-sortable-helper):not(.js-grid-row-holder), .in-drop-place',
            ).length;
            const $rowHolder = $(this).find('.js-grid-row-holder');
            $rowHolder.toggle(gridRowsCount === 0);
        });
    }

    onUpdate($content) {
        $content.find('.js-multiple-grids-save-all-button').prop('disabled', false);
        FormChangeInfo.showInfo();
        this.toggleRowHolders($content);
    }

    static init($content) {
        // eslint-disable-next-line no-new
        new GridMultipleDragAndDrop($content);
    }
}

new Register().registerCallback(GridMultipleDragAndDrop.init, 'GridMultipleDragAndDrop.init');
