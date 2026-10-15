import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import { Modal } from '@tabler/core';
import Translator from 'bazinga-translator';
import FormChangeInfo from 'framework/admin/components/FormChangeInfo';
import ConfirmWindow from '../js/utils/confirmWindow';

/*
 * The widget of the ContentEditorType (templates/form/type/ContentEditorType.html.twig): opens the editor as a full-screen overlay
 * and hands the document over to the hidden input of the edited entity when the editor is done.
 * Closing the overlay without it discards the changes made in the editor.
 */
export default class extends Controller {
    static targets = ['modal'];

    static values = { targetInputId: String, openOnConnect: Boolean };

    connect() {
        this.input = document.getElementById(this.targetInputIdValue);
        this.loadedDocument = this.input.value;
        this.onDone = event => this.applyDocument(event.detail.document);

        this.element.addEventListener('content-editor:done', this.onDone);

        // the entity was refused, the editor shows what is wrong with the content
        if (this.openOnConnectValue) {
            this.open();
        }
    }

    disconnect() {
        this.element.removeEventListener('content-editor:done', this.onDone);
    }

    open() {
        this.openedDocument = this.currentDocument();
        Modal.getOrCreateInstance(this.modalTarget).show();
    }

    close() {
        if (this.currentDocument() === this.openedDocument) {
            this.hide();

            return;
        }

        ConfirmWindow.show({
            content: Translator.trans('You have unsaved changes in the content, discard them?'),
            continueEvent: () => this.discardChanges(),
        });
    }

    // the editor goes back to the document of the hidden input, so the next opening does not show the discarded changes
    async discardChanges() {
        const component = await getComponent(this.editorElement());
        await component.action('discard', { documentJson: this.input.value });
        this.hide();
    }

    hide() {
        Modal.getOrCreateInstance(this.modalTarget).hide();
    }

    editorElement() {
        return this.modalTarget.querySelector('[data-controller~="content-editor"]');
    }

    // the editor keeps the exported document in its attribute, the Live Component updates it with every render
    currentDocument() {
        return this.editorElement().dataset.contentEditorExportedDocumentValue;
    }

    applyDocument(documentJson) {
        this.input.value = documentJson;

        if (documentJson !== this.loadedDocument) {
            FormChangeInfo.showInfo();
        }

        this.hide();
    }
}
