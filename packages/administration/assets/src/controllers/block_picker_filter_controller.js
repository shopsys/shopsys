import { Controller } from '@hotwired/stimulus';
import matchesPickerFilter from '../js/contentEditor/matchesPickerFilter';

/*
 * The filter of the block picker (templates/content_editor/picker.html.twig), works on the tiles already in the page.
 */
export default class extends Controller {
    static targets = ['input', 'tile', 'group'];

    connect() {
        this.onShown = () => this.inputTarget.focus();
        this.onHidden = () => this.clear();

        this.element.addEventListener('shown.bs.modal', this.onShown);
        this.element.addEventListener('hidden.bs.modal', this.onHidden);
    }

    disconnect() {
        this.element.removeEventListener('shown.bs.modal', this.onShown);
        this.element.removeEventListener('hidden.bs.modal', this.onHidden);
    }

    filter() {
        const query = this.inputTarget.value;

        this.tileTargets.forEach(tile => {
            tile.classList.toggle('d-none', !matchesPickerFilter(tile.textContent, query));
        });
        this.groupTargets.forEach(group => {
            group.classList.toggle(
                'd-none',
                group.querySelector('[data-block-picker-filter-target="tile"]:not(.d-none)') === null,
            );
        });
    }

    // Enter inserts the first block left, it must not submit a form around the editor
    chooseFirst(event) {
        event.preventDefault();
        this.tileTargets.find(tile => !tile.classList.contains('d-none'))?.click();
    }

    clear() {
        this.inputTarget.value = '';
        this.filter();
    }
}
