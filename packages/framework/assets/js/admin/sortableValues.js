import Register from '../common/register';
import { escapeHtml } from '../common/components/escape';

export default class SortableValues {

    constructor ($container) {
        $container.filterAllNodes('.js-sortable-values-item-add').click((event) => this.addItemButtonClick(event));
        $container.filterAllNodes('.js-sortable-values-item-remove').click(() => this.removeItemButtonClick());

        $container.filterAllNodes('.js-sortable-values-items').sortable({
            items: '.js-sortable-values-item',
            handle: '.js-sortable-values-item-handle'
        });
    }

    addItemButtonClick (event) {
        const $button = $(event.currentTarget);
        const $container = $button.closest('.js-sortable-values-container');
        const $option = $container.find('.js-sortable-values-input :selected');

        if ($option.val()) {
            const $item = this.createItem($button.data('item-template'), $option.val(), $option.text());

            $container.find('.js-sortable-values-items').prepend($item);
            (new Register()).registerNewContent($item);
            this.disableOption($option);
        }
    }

    createItem (html, value, label) {
        html = html.replace(/%value%/g, escapeHtml(value));
        html = html.replace(/%label%/g, escapeHtml(label));

        return $($.parseHTML(html));
    }

    removeItemButtonClick (event) {
        const $item = $(event.currentTarget).closest('.js-sortable-values-item');
        this.enableOptionOfItem($item);

        $item.remove();
    }

    disableOption ($option) {
        const $select = $option.closest('.js-sortable-values-input');

        $option.prop('disabled', true);
        $select.val('').trigger('change.select2');
    }

    enableOptionOfItem ($item) {
        const $container = $item.closest('.js-sortable-values-container');
        const $input = $item.find('.js-sortable-values-item-input');
        const $option = $container.find('.js-sortable-values-input [value="' + $input.val() + '"]');

        $option.prop('disabled', false);
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new SortableValues($container);
    }
}

(new Register()).registerCallback(SortableValues.init);
