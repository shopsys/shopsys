import $ from 'jquery';
import Sortable from 'sortablejs';
import { escapeHtml } from '../../common/utils/escapeHtml';
import Register from '../../common/utils/Register';

export default class SortableValues {
    constructor($container) {
        $container.filterAllNodes('.js-sortable-values-input').change(event => this.addItemSelectChange(event));
        $container.filterAllNodes('.js-sortable-values-item-remove').click(event => this.removeItemButtonClick(event));

        $container.filterAllNodes('.js-sortable-values-items').each((_index, element) => {
            Sortable.create(element, {
                handle: '.js-sortable-values-item-handle',
                draggable: '.js-sortable-values-item',
                animation: 150,
            });
        });
    }

    addItemSelectChange(event) {
        const $select = $(event.currentTarget);
        const $container = $select.closest('.js-sortable-values-container');
        const $option = $container.find('.js-sortable-values-input :selected');

        if ($option.val()) {
            const $item = this.createItem($select.data('item-template'), $option.val(), $option.text());

            $container.find('.js-sortable-values-items').prepend($item);
            new Register().registerNewContent($item);
            this.disableOption($option);
        }
    }

    createItem(html, value, label) {
        html = html.replace(/%value%/g, escapeHtml(value));
        html = html.replace(/%label%/g, escapeHtml(label));

        return $($.parseHTML(html));
    }

    removeItemButtonClick(event) {
        const $item = $(event.currentTarget).closest('.js-sortable-values-item');
        this.enableOptionOfItem($item);

        $item.remove();
    }

    disableOption($option) {
        const $select = $option.closest('.js-sortable-values-input');
        const tomSelectInstance = $select[0].tomselect;

        tomSelectInstance.clear();

        this.updateDisabledSelectOption(tomSelectInstance, $option, true);
    }

    enableOptionOfItem($item) {
        const $container = $item.closest('.js-sortable-values-container');
        const $input = $item.find('.js-sortable-values-item-input');
        const $option = $container.find(`.js-sortable-values-input [value="${$input.val()}"]`);

        const $select = $option.closest('.js-sortable-values-input');
        const tomSelectInstance = $select[0].tomselect;
        this.updateDisabledSelectOption(tomSelectInstance, $option, false);
    }

    updateDisabledSelectOption(tomSelectInstance, $option, disabled) {
        tomSelectInstance.updateOption($option.val(), {
            value: $option.val(),
            text: $option.text(),
            disabled: disabled,
        });
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new SortableValues($container);
    }
}

new Register().registerCallback(SortableValues.init, 'SortableValues.init');
