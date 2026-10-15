import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import { Modal } from '@tabler/core';
import matchesPickerFilter from '../js/contentEditor/matchesPickerFilter';

/*
 * The block picker (templates/content_editor/picker.html.twig) on the root of the editor: the "Add block" buttons open it,
 * it remembers where the block goes, filters the tiles already in the page and adds the chosen block through the Live Component.
 */
export default class extends Controller {
    static targets = ['modal', 'input', 'tile', 'group'];

    connect() {
        this.slotPath = '';
        this.index = 0;
        this.onShown = () => this.inputTarget.focus();
        this.onHidden = () => this.clear();

        this.modalTarget.addEventListener('shown.bs.modal', this.onShown);
        this.modalTarget.addEventListener('hidden.bs.modal', this.onHidden);
    }

    disconnect() {
        this.modalTarget.removeEventListener('shown.bs.modal', this.onShown);
        this.modalTarget.removeEventListener('hidden.bs.modal', this.onHidden);
    }

    // the buttons carry the slot ("" for the document) and the position of the new block
    open({ currentTarget: { dataset } }) {
        this.slotPath = dataset.slotPath;
        this.index = Number(dataset.index);
        this.filter();
        Modal.getOrCreateInstance(this.modalTarget).show();
    }

    async add({ currentTarget: { dataset } }) {
        Modal.getOrCreateInstance(this.modalTarget).hide();

        const component = await getComponent(this.element);
        await component.action('addBlock', { type: dataset.type, slotPath: this.slotPath, index: this.index });
    }

    // the containers are not offered inside of a slot
    filter() {
        const query = this.inputTarget.value;

        this.tileTargets.forEach(tile => {
            const isHidden =
                !matchesPickerFilter(tile.textContent, query) ||
                (this.slotPath !== '' && tile.hasAttribute('data-container'));

            tile.classList.toggle('d-none', isHidden);
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
