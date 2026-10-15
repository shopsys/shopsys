import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

/*
 * The MediaType widget: opens the media picker and shows the media chosen in it.
 * Used by form/type/MediaType.html.twig, the picker component must be rendered on the same page.
 */
export default class extends Controller {
    static targets = ['input', 'preview', 'title', 'removeButton'];

    connect() {
        this.onMediaSelected = event => this.applySelection(event.detail);

        window.addEventListener('content-editor:media-selected', this.onMediaSelected);
        this.render();
    }

    disconnect() {
        window.removeEventListener('content-editor:media-selected', this.onMediaSelected);
    }

    async choose() {
        // the picker of the editor the field is in, a field outside of an editor uses the first picker of the page
        const pickerSelector = '[data-live-name-value="Admin:ContentEditor:MediaPicker"]';
        const pickerElement =
            this.element.closest('[data-content-editor-widget]')?.querySelector(pickerSelector) ??
            document.querySelector(pickerSelector);
        const picker = await getComponent(pickerElement);

        picker.emitSelf('content-editor:media-picker:open', { targetInputId: this.inputTarget.id });
    }

    remove() {
        this.inputTarget.value = '';
        this.previewTarget.removeAttribute('src');
        this.titleTarget.textContent = '';
        this.render();
    }

    applySelection({ targetInputId, mediaId, url, title }) {
        if (targetInputId !== this.inputTarget.id) {
            return;
        }

        this.inputTarget.value = mediaId;
        this.previewTarget.src = `${url}?width=240`;
        this.titleTarget.textContent = title;
        this.inputTarget.dispatchEvent(new Event('change', { bubbles: true }));
        this.render();
    }

    render() {
        const hasMedia = this.inputTarget.value !== '';

        this.previewTarget.classList.toggle('d-none', !this.previewTarget.getAttribute('src'));
        this.removeButtonTarget.classList.toggle('d-none', !hasMedia);
    }
}
