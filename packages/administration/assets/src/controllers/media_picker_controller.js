import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

/*
 * Uploads a new image from the media picker modal: the file goes to the admin upload endpoint
 * and the returned temporary filename is handed to the createFromTemporaryFile live action,
 * which stores it in the library and selects it.
 * Used by content_editor/media_picker.html.twig.
 */
export default class extends Controller {
    static targets = ['fileInput', 'uploadButton', 'error'];
    static values = { uploadUrl: String, errorMessage: String };

    chooseFile() {
        this.fileInputTarget.click();
    }

    async upload() {
        const file = this.fileInputTarget.files[0];

        if (!file) {
            return;
        }

        this.errorTarget.classList.add('d-none');
        this.uploadButtonTarget.disabled = true;

        try {
            const temporaryFilename = await this.uploadFile(file);
            const component = await getComponent(this.element.closest('[data-controller~="live"]'));

            await component.action('createFromTemporaryFile', {
                temporaryFilename,
                title: file.name.replace(/\.[^.]+$/, ''),
            });
        } catch (error) {
            this.errorTarget.textContent = error.message || this.errorMessageValue;
            this.errorTarget.classList.remove('d-none');
        } finally {
            this.uploadButtonTarget.disabled = false;
            this.fileInputTarget.value = '';
        }
    }

    async uploadFile(file) {
        const body = new FormData();
        body.append('file', file);

        const response = await fetch(this.uploadUrlValue, { method: 'POST', body });
        const result = await response.json();

        if (result.status !== 'success') {
            throw new Error(result.message || this.errorMessageValue);
        }

        return result.filename;
    }
}
