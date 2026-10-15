import { Controller } from '@hotwired/stimulus';
import { Modal } from '@tabler/core';
import FormChangeInfo from 'framework/admin/components/FormChangeInfo';

/*
 * The widget of the ContentEditorType (templates/form/type/ContentEditorType.html.twig): opens the editor as a full-screen overlay
 * and hands the document over to the hidden input of the edited entity when the editor is done.
 */
export default class extends Controller {
    static targets = ['modal'];

    static values = { targetInputId: String, openOnConnect: Boolean };

    connect() {
        this.input = document.getElementById(this.targetInputIdValue);
        this.loadedDocument = this.input.value;
        this.doneEvent = `content-editor:done:${this.targetInputIdValue}`;
        this.onDone = event => this.applyDocument(event.detail.document);

        window.addEventListener(this.doneEvent, this.onDone);

        // the entity was refused, the editor shows what is wrong with the content
        if (this.openOnConnectValue) {
            this.open();
        }
    }

    disconnect() {
        window.removeEventListener(this.doneEvent, this.onDone);
    }

    open() {
        Modal.getOrCreateInstance(this.modalTarget).show();
    }

    applyDocument(documentJson) {
        this.input.value = documentJson;

        if (documentJson !== this.loadedDocument) {
            FormChangeInfo.showInfo();
        }

        Modal.getOrCreateInstance(this.modalTarget).hide();
    }
}
